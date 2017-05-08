<?php
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\CsvMigrationsUtils;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);
echo $this->Html->script(
    [
        'Translations.translation'
    ],
    [
        'block' => 'scriptBotton'
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
        <div class="box box-default">
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

                            $label = $fhf->renderName(
                                $tableName,
                                $field['name'],
                                $renderOptions
                            );

                            ?>
                            <div class="col-xs-4 col-md-2 text-right">
                                <strong><?= $label?>:</strong>
                            </div>
                            <div class="col-xs-8 col-md-4">
                            <?php
                            $value = $fhf->renderValue(
                                $tableName,
                                $field['name'],
                                $options['entity'], //->{$field['name']},
                                $renderOptions
                            );
                            echo $value;
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
        $event = new Event('CsvMigrations.View.View.TabsList', $this, [
            'request' => $this->request,
            'entity' => $options['entity'],
            'options' => []
        ]);

        $this->eventManager()->dispatch($event);
        $tabs = $event->result['tabs'];

        echo $this->Html->css('AdminLTE./plugins/datatables/dataTables.bootstrap', ['block' => 'css']);
        echo $this->Html->script(
            [
                'AdminLTE./plugins/datatables/jquery.dataTables.min',
                'AdminLTE./plugins/datatables/dataTables.bootstrap.min',
            ],
            [
                'block' => 'scriptBotton'
            ]
        );
        echo $this->Html->scriptBlock(
            '
                var url = document.location.toString();
                if (matches = url.match(/(.*)(#.*)/)) {
                    $(".nav-tabs a[href=\'" + matches["2"] + "\']").tab("show");
                    history.pushState("", document.title, window.location.pathname + window.location.search);
                }
            ',
            [
                'block' => 'scriptBotton'
            ]
        );
    ?>
        <?php if (!empty($tabs)) : ?>
        <div class="nav-tabs-custom">
            <ul id="relatedTabs" class="nav nav-tabs" role="tablist">
            <?php foreach ($tabs as $k => $tab) :?>
                <li role="presentation" class="<?= ($k == 0) ? 'active' : ''?>">
                    <a href="#<?= $tab['containerId']?>" role="tab" data-toggle="tab"><?= $tab['label']?></a>
                </li>
            <?php endforeach; ?>
            </ul>

            <div class="tab-content">
                <?php foreach ($tabs as $k => $tab) :?>
                    <div role="tabpanel" class="tab-pane <?= ($k == 0) ? 'active' : ''?>" id="<?= $tab['containerId']?>">
                        <?php
                        $beforeTabContentEvent = new Event('CsvMigrations.View.View.TabContent.beforeContent', $this, [
                            'request' => $this->request,
                            'entity' => $options['entity'],
                            'options' => [
                                    'tab' => $tab
                                ]
                        ]);

                        $this->eventManager()->dispatch($beforeTabContentEvent);
                        $beforeTab = $beforeTabContentEvent->result;

                        if (isset($beforeTab['content']['length']) && count($beforeTab['content']['length']) > 0) {
                            echo $this->cell('CsvMigrations.TabContent', [
                            [
                                'request' => $this->request,
                                'content' => $beforeTab['content'],
                                'tab' => $tab,
                                'options' => ['order' => 'beforeContent', 'title' => $beforeTab['title']],
                                'entity' => $options['entity'],
                            ]
                            ]);
                        }

                        $tabContentEvent = new Event('CsvMigrations.View.View.TabContent', $this, [
                            'request' => $this->request,
                            'entity' => $options['entity'],
                            'options' => [
                                    'tab' => $tab
                                ]
                        ]);

                        $this->eventManager()->dispatch($tabContentEvent);
                        $content = $tabContentEvent->result;

                        if (!empty($content) && !isset($content['rawOutput'])) {
                            echo $this->cell('CsvMigrations.TabContent', [
                            [
                                'request' => $this->request,
                                'content' => $content,
                                'tab' => $tab,
                                'options' => ['order' => 'tabContent'],
                                'entity' => $options['entity'],
                            ]
                            ]);
                        }

                        if (!empty($content['rawOutput'])) {
                            echo $content['rawOutput'];
                        }

                        if (!empty($content)) {
                            echo $this->Html->scriptBlock(
                                '$(".' . $tab['containerId'] . '").DataTable({
                                        "paging": true,
                                        "searching": false
                                    });',
                                ['block' => 'scriptBotton']
                            );
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div> <!-- .tab-content -->
        </div> <!-- .nav-tabs-custom -->
        <?php endif; ?>
    </div>
</div> <!-- .associated-records -->
<?php endif;?>
<?php if (empty($this->request->query['embedded'])) : ?>
</section>
<?php endif;?>
<?php
// Event dispatcher for bottom section
$event = new Event('View.View.Body.Bottom', $this, ['request' => $this->request, 'options' => $options]);
$this->eventManager()->dispatch($event);
echo $event->result;

$modalBody = $this->requestAction([
            'plugin' => 'Translations',
            'controller' => 'Translations',
            'action' => 'add'
        ], [
            'query' => [
                'embedded' => 'Translations',
                'foreign_key' => 'object_foreign_key',
                'modal_id' => 'translations_translate_id_modal',
            ]
        ]);
?>
<div id="translations_translate_id_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h2 class="modal-title"><?= __('Manage Translations') ?></h2>
            </div> <!-- modal-header -->
            <div class="modal-body">
                <?= $modalBody ?>
            </div>
        </div> <!-- modal-content -->
    </div> <!-- modal-dialog -->
</div> <!-- modal window -->
