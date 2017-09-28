<?php
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\CsvMigrationsUtils;
use CsvMigrations\Event\EventName;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);
echo $this->Html->script(
    [
        'Translations.translation'
    ],
    [
        'block' => 'scriptBottom'
    ]
);

$defaultOptions = [
    'title' => null,
    'entity' => null,
    'fields' => [],
];
if (empty($options)) {
    $options = [];
}
$options = array_merge($defaultOptions, $options);

// Get plugin name
if (empty($options['plugin'])) {
    $options['plugin'] = $this->request->plugin;
}

// Get controller name
if (empty($options['controller'])) {
    $options['controller'] = $this->request->controller;
}
// Get full controller name, including plugin prefix
$controllerName = $options['controller'];
if (!empty($options['plugin'])) {
    $controllerName = $options['plugin'] . '.' . $controllerName;
}
// Get display field
$displayField = TableRegistry::get($controllerName)->displayField();
// Get title
if (empty($options['title'])) {
    $options['title'] = $this->Html->link(
        Inflector::humanize(Inflector::underscore($moduleAlias)),
        ['plugin' => $options['plugin'], 'controller' => $options['controller'], 'action' => 'index']
    );
    $options['title'] .= ' &raquo; ';
    $value = $fhf->renderValue(
        !is_null($options['plugin']) ? $options['plugin'] . '.' . $options['controller'] : $options['controller'],
        $displayField,
        $options['entity']->$displayField,
        ['entity' => $options['entity']]
    );
    $options['title'] .= $value;
}
?>
<?php if (empty($this->request->query['embedded'])) : ?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= $options['title'] ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
            <div class="btn-group btn-group-sm" role="group">
                <?= $this->element('CsvMigrations.Menu/view_top', [
                    'options' => $options,
                    'user' => $user,
                    'displayField' => $displayField
                ]); ?>
            </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
<?php endif; ?>
        <?php
        if (!empty($options['fields'])) :
            $embeddedFields = [];
            $embeddedDirty = false;
            foreach ($options['fields'] as $panelName => $panelFields) :
                if (!empty($this->request->query['embedded'])) {
                    $panelName = Inflector::singularize(
                        Inflector::humanize($this->request->controller)
                    ) . ' : ' . $panelName;
                }

        ?>
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $panelName; ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
            <?php foreach ($panelFields as $subFields) : ?>
                <div class="row">
                <?php foreach ($subFields as $field) : ?>
                    <?php if ('' !== trim($field['name']) && !$embeddedDirty) : ?>
                        <?php
                        // embedded field
                        if ('EMBEDDED' === $field['name']) {
                            $embeddedDirty = true;
                        }
                        ?>
                        <?php if (!$embeddedDirty) : // non-embedded field ?>
                            <?php

                            $tableName = $field['model'];
                            if (!is_null($field['plugin'])) {
                                $tableName = $field['plugin'] . '.' . $tableName;
                            }
                            $renderOptions = [
                                'entity' => $options['entity'],
                                'imageSize' => 'small',
                            ];
                            ?>
                            <div class="col-xs-4 col-md-2 text-right">
                                <strong><?= $fhf->renderName($tableName, $field['name'], $renderOptions); ?>:</strong>
                            </div>
                            <div class="col-xs-8 col-md-4">
                            <?php
                            $value = $fhf->renderValue(
                                $tableName,
                                $field['name'],
                                $options['entity'], //->{$field['name']},
                                $renderOptions
                            );
                            $fieldName = $field['name'];
                            $event = new Event((string)EventName::VIEW_TRANSLATION_BUTTON(), $this, [
                                'model' => $tableName,
                                'options' => [
                                    'record_id' => $options['entity']->id,
                                    'field_name' => $fieldName,
                                    'field_value' => $options['entity']->$fieldName,
                                    'user' => $user,
                                ]
                            ]);

                            $this->eventManager()->dispatch($event);
                            $translationButton = $event->result;

                            echo $translationButton . $value;
                            echo empty($value) ? '&nbsp;' : '';
                            ?>
                            </div>
                        <?php endif; ?>
                    <?php elseif ('' !== trim($field['name'])) : ?>
                            <?php
                            $embeddedFields[] = $field['name'];
                            $embeddedDirty = false;
                            ?>
                    <?php else : ?>
                            <div class="col-xs-4 col-md-2 text-right">&nbsp;</div>
                            <div class="col-xs-8 col-md-4">&nbsp;</div>
                    <?php endif; ?>
                    <div class="clearfix visible-xs visible-sm"></div>
                <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
            <?php
            if (empty($embeddedFields)) {
                continue;
            }

            // Fetch embedded module(s) using CakePHP's requestAction() method
            foreach ($embeddedFields as $embeddedField) {
                $embeddedFieldName = substr($embeddedField, strrpos($embeddedField, '.') + 1);
                list($embeddedPlugin, $embeddedController) = pluginSplit(
                    substr($embeddedField, 0, strrpos($embeddedField, '.'))
                );

                $embeddedAssocName = CsvMigrationsUtils::createAssociationName(
                    $embeddedPlugin . $embeddedController,
                    $embeddedFieldName
                );

                // @note this only works for belongsTo for now.
                $embeddedAssocName = Inflector::underscore(Inflector::singularize($embeddedAssocName));

                if (!empty($options['entity']->$embeddedFieldName)) {
                    try {
                        echo $this->requestAction(
                            [
                                'plugin' => $embeddedPlugin,
                                'controller' => $embeddedController,
                                'action' => $this->request->action
                            ],
                            [
                                'query' => ['embedded' => $this->request->controller . '.' . $embeddedAssocName],
                                'pass' => [$options['entity']->$embeddedFieldName]
                            ]
                        );
                    } catch (RecordNotFoundException $e) {
                        // just don't display anything if embedded record was not found
                    } catch (ForbiddenException $e) {
                        // just don't display anything if current user has no access to embedded record
                    }
                }
            }
            $embeddedFields = [];
            endforeach;
        endif;
?>
<?php if (empty($this->request->query['embedded'])) : ?>
<?php
// loading common setup for typeahead/panel/etc libs for tabs
echo $this->element('CsvMigrations.common_js_libs');
?>
<hr />
<div class="row associated-records">
    <div class="col-xs-12">
    <?php
        $event = new Event((string)EventName::VIEW_TABS_LIST(), $this, [
            'request' => $this->request,
            'entity' => $options['entity'],
            'user' => $user,
            'options' => []
        ]);

        $this->eventManager()->dispatch($event);
        $tabs = $event->result['tabs'];

    if (!empty($tabs)) {
        echo $this->Html->scriptBlock(
            '
                var url = document.location.toString();
                if (matches = url.match(/(.*)(#.*)/)) {
                    $(".nav-tabs a[href=\'" + matches["2"] + "\']").tab("show");
                    history.pushState("", document.title, window.location.pathname + window.location.search);
                }
            ',
            [
                'block' => 'scriptBottom'
            ]
        );
    ?>
        <div class="nav-tabs-custom">
            <ul id="relatedTabs" class="nav nav-tabs" role="tablist">
            <?php $active = true; ?>
            <?php foreach ($tabs as $tab) : ?>
                <li role="presentation" class="<?= $active ? 'active' : ''?>">
                    <?= $this->Html->link($tab['label'], "#{$tab['containerId']}", ['role' => 'tab', 'data-toggle' => "tab", 'escape' => false]);?>
                </li>
                <?php $active = false; ?>
            <?php endforeach; ?>
            </ul>

            <div class="tab-content">
                <?php $active = true; ?>
                <?php foreach ($tabs as $tab) : ?>
                    <div role="tabpanel" class="tab-pane <?= $active ? 'active' : ''?>" id="<?= $tab['containerId']?>">
                        <?php
                        $tabContentEvent = new Event((string)EventName::VIEW_TAB_CONTENT(), $this, [
                            'request' => $this->request,
                            'entity' => $options['entity'],
                            'user' => $user,
                            'options' => [
                                    'tab' => $tab
                                ]
                        ]);

                        $this->eventManager()->dispatch($tabContentEvent);
                        $content = $tabContentEvent->result;

                        echo $content;
                        ?>
                    </div>
                    <?php $active = false; ?>
                <?php endforeach; ?>
            </div> <!-- .tab-content -->
        </div> <!-- .nav-tabs-custom -->
    <?php } ?>
    </div>
</div> <!-- .associated-records -->
<?php endif;?>
<?php if (empty($this->request->query['embedded'])) : ?>
</section>
<?php endif;?>
<?php
// Event dispatcher for bottom section
$event = new Event(
    (string)EventName::VIEW_BODY_BOTTOM(),
    $this,
    ['request' => $this->request, 'options' => $options]
);
$this->eventManager()->dispatch($event);

echo $event->result;
?>
