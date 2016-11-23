<?php
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Inflector;
use Cake\ORM\TableRegistry;
use CsvMigrations\CsvMigrationsUtils;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);

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
// Get title
if (empty($options['title'])) {
    $controllerName = $options['controller'];
    if (!empty($options['plugin'])) {
        $controllerName = $options['plugin'] . '.' . $controllerName;
    }
    $displayField = TableRegistry::get($controllerName)->displayField();

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

<div class="row">
    <div class="col-xs-12">
        <?php if (empty($this->request->query['embedded'])) : ?>
        <div class="row">
            <div class="col-xs-6">
                <h3><strong><?= $options['title'] ?></strong></h3>
            </div>
            <div class="col-xs-6">
                <div class="h3 text-right">
                <?php
                    $event = new Event('View.View.Menu.Top', $this, [
                        'request' => $this->request,
                        'options' => $options
                    ]);
                    $this->eventManager()->dispatch($event);
                    if (!empty($event->result)) {
                        echo $event->result;
                    }
                ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php
        if (!empty($options['fields'])) :
            $embeddedFields = [];
            $embeddedDirty = false;
            foreach ($options['fields'] as $panelName => $panelFields) :
             if (!empty($this->request->query['embedded'])) {
                $panelName = Inflector::singularize(Inflector::humanize($this->request->controller)) . ' : ' . $panelName;
             }

        ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <strong><?= $panelName; ?></strong>
                </h3>
            </div>
            <div class="panel-body">
            <?php foreach ($panelFields as $subFields) : ?>
                <div class="row">
                <?php
                foreach ($subFields as $field) :
                    if ('' !== trim($field['name']) && !$embeddedDirty) :
                        /*
                        embedded field
                         */
                        if ('EMBEDDED' === $field['name']) {
                            $embeddedDirty = true;
                        }

                        /*
                        non-embedded field
                         */
                        if (!$embeddedDirty) :
                ?>
                        <div class="col-xs-4 col-md-2 text-right">
                            <strong><?= Inflector::humanize($field['name']) ?>:</strong>
                        </div>
                        <div class="col-xs-8 col-md-4">
                        <?php
                            $tableName = $field['model'];
                            if (!is_null($field['plugin'])) {
                                $tableName = $field['plugin'] . '.' . $tableName;
                            }
                            $renderOptions = [
                                'entity' => $options['entity'],
                                'imageSize' => 'small'
                            ];
                            $value = $fhf->renderValue(
                                $tableName,
                                $field['name'],
                                $options['entity']->{$field['name']},
                                $renderOptions
                            );
                            echo $value;
                            echo empty($value) ? '&nbsp;' : '';
                        ?>
                        </div>
                    <?php endif; ?>
                    <?php elseif ('' !== trim($field['name'])) :
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

            /*
            Fetch embedded module(s) using CakePHP's requestAction() method
             */
            foreach ($embeddedFields as $embeddedField) {
                $embeddedFieldName = substr($embeddedField, strrpos($embeddedField, '.') + 1);
                list($embeddedPlugin, $embeddedController) = pluginSplit(
                    substr($embeddedField, 0, strrpos($embeddedField, '.'))
                );

                $embeddedAssocName = CsvMigrationsUtils::createAssociationName(
                    $embeddedPlugin . $embeddedController,
                    $embeddedFieldName
                );

                /*
                @note this only works for belongsTo for now.
                 */
                $embeddedAssocName = Inflector::underscore(Inflector::singularize($embeddedAssocName));

                if (!empty($options['entity']->$embeddedFieldName)) {
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
                }
            }
            $embeddedFields = [];
            ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($this->request->query['embedded'])) : ?>
<div class="row associated_records">
    <div class="col-xs-12">
    <hr/>
    <?php
        $event = new Event('CsvMigrations.View.View.TabsList', $this, [
            'request' => $this->request,
            'entity' => $options['entity'],
            'options' => []
        ]);

        $this->eventManager()->dispatch($event);
        $tabs = $event->result['tabs'];

        echo $this->Html->css('CsvMigrations.datatables.min', ['block' => 'cssBottom']);
        echo $this->Html->script('CsvMigrations.datatables.min', ['block' => 'scriptBottom']);

        if (!empty($tabs)) { ?>
            <ul id="relatedTabs" class="nav nav-tabs" role="tablist">
            <?php foreach ($tabs as $k => $tab) :?>
                <li role="presentation" class="<?= ($k == 0) ? 'active' : ''?>">
                    <a href="#<?= $tab['containerId']?>" role="tab" data-toggle="tab"><?= $tab['label']?></a>
                </li>
            <?php endforeach; ?>
            </ul>

            <div class="tab-content">
                <?php foreach($tabs as $k => $tab) :?>
                    <div role="tabpanel" class="tab-pane <?= ($k == 0) ? 'active' : ''?>" id="<?= $tab['containerId']?>">
                        <?php
                            /* getting before content data if any required */
                            $beforeTabContentEvent = new Event('CsvMigrations.View.View.TabContent.beforeContent', $this, [
                                'data' => [],
                            ]);
                            $this->eventManager()->dispatch($beforeTabContentEvent);
                            $beforeTab = $beforeTabContentEvent->result;

                            if (!empty($beforeTab)) {
                                echo $this->cell('CsvMigrations.TabContent', [
                                    'request' => $this->request,
                                    'content' => $beforeTab,
                                    'tab' => $tab,
                                    'options' => [
                                        'order' => 'beforeContent',
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

                            if (!empty($content)) {
                                echo $this->cell('CsvMigrations.TabContent', [
                                    [
                                        'request' => $this->request,
                                        'content' => $content,
                                        'tab' => $tab,
                                        'options' => [],
                                    ]
                                ]);

                                echo $this->Html->scriptBlock(
                                    '$(\'.' . $tab['containerId']. '\').DataTable({
                                        "paging":false,
                                        "searching": false
                                    });',
                                    ['block' => 'scriptBottom']
                                );
                            }

                            /* getting after content data if any */
                            $afterTabContentEvent = new Event('CsvMigrations.View.View.TabContent.afterContent', $this, [
                                'data' => [],
                            ]);
                            $this->eventManager()->dispatch($afterTabContentEvent);
                            $afterContent = $afterTabContentEvent->result;

                            if (!empty($afterContent)) {
                                 echo $this->cell('CsvMigrations.TabContent', [
                                    'request' => $this->request,
                                    'content' => $beforeTab,
                                    'tab' => $tab,
                                    'options' => [
                                        'order' => 'afterContent',
                                    ]
                                ]);
                            }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div> <!-- .tab-content -->
        <?php } ?>
    </div>
    <?php
        //loading common setup for typeahead/panel/etc libs for tabs
        echo $this->element('CsvMigrations.common_js_libs');
    ?>
</div> <!-- associated records -->
<?php endif ;?>

<?php
    // Event dispatcher for bottom section
    $event = new Event('View.View.Body.Bottom', $this, ['request' => $this->request, 'options' => $options]);
    $this->eventManager()->dispatch($event);
    echo $event->result;
?>

<?= $this->Html->css('CsvMigrations.style', ['block' => 'cssBottom']); ?>
