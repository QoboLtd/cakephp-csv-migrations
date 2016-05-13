<?php
use Cake\Event\Event;
use Cake\Utility\Inflector;
use Cake\ORM\TableRegistry;
use CsvMigrations\CsvMigrationsUtils;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory();

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
    $options['title'] .= $options['entity']->$displayField;
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
                        if ('EMBEDDED' === $field['name']) {
                            $embeddedDirty = true;
                        }
                ?>
                        <div class="col-xs-4 col-md-2 text-right">
                            <?php
                            $label = Inflector::humanize($field['name']);
                            if ($this->request->controller !== $field['model'])
                                $label = Inflector::humanize($field['model']) . ' ' . $label;
                            ?>
                            <strong><?= $label ?>:</strong>
                        </div>
                        <div class="col-xs-8 col-md-4">
                        <?php
                            $tableName = $field['model'];
                            if (!is_null($field['plugin'])) {
                                $tableName = $field['plugin'] . '.' . $tableName;
                            }
                            $value = $fhf->renderValue($tableName, $field['name'], $options['entity']->$field['name']);
                            echo !empty($value) ? $value : '&nbsp;';
                        ?>
                        </div>
                    <?php elseif ('' !== trim($field['name'])) :
                        $embeddedFields[] = $field['name'];
                        $embeddedDirty = false;
                    ?>
                        <div class="col-xs-4 col-md-2 text-right">&nbsp;</div>
                        <div class="col-xs-8 col-md-4">&nbsp;</div>
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
    <?= $this->element('CsvMigrations.associated_records'); ?>
<?php endif; ?>