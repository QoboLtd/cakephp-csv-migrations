<?php
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
    $displayField = TableRegistry::get($options['controller'])->displayField();

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
        <h3><strong><?= $options['title'] ?></strong></h3>
        <?php
            /*
            @todo probably this needs to be added to the View using Event Listeners
             */
            $changelogElement = 'changelog';
            if ($this->elementExists($changelogElement)) {
                echo $this->element($changelogElement, ['recordId' => $options['entity']->id]);
            }
        ?>
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

<?= $this->element('CsvMigrations.associated_records'); ?>