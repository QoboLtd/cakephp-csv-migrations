<?php
use Cake\Event\Event;
use Cake\Utility\Inflector;
use Cake\ORM\TableRegistry;
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
                <?php foreach ($subFields as $field) : ?>
                    <?php if ('' !== trim($field)) : ?>
                        <div class="col-xs-4 col-md-2 text-right">
                            <strong><?= Inflector::humanize($field); ?>:</strong>
                        </div>
                        <div class="col-xs-8 col-md-4">
                        <?php
                            $tableName = $this->name;
                            if (!is_null($this->plugin)) {
                                $tableName = $this->plugin . '.' . $tableName;
                            }
                            $value = $fhf->renderValue($tableName, $field, $options['entity']->$field);
                            echo !empty($value) ? $value : '&nbsp;';
                        ?>
                        </div>
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
        <?php endforeach; endif; ?>
    </div>
</div>
<?php if (empty($this->request->query['embedded'])) : ?>
    <?= $this->element('CsvMigrations.associated_records'); ?>
<?php endif; ?>