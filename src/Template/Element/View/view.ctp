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
        Inflector::humanize(Inflector::underscore($options['controller'])),
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
            if (!empty($options['fields'])) :
                foreach ($options['fields'] as $panelName => $panelFields) :
        ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <strong><?= Inflector::humanize($panelName); ?></strong>
                </h3>
            </div>
            <table class="table table-hover">
            <?php foreach ($panelFields as $subFields) : ?>
                <tr>
                <?php foreach ($subFields as $field) : if ('' !== trim($field)) : ?>
                    <td class="col-xs-3 text-right">
                        <strong><?= Inflector::humanize($field); ?>:</strong>
                    </td>
                    <td class="col-xs-3">
                        <?= $fhf->renderValue($this->name, $field, $options['entity']->$field); ?>
                    </td>
                        <?php else : ?>
                    <td class="col-xs-3">&nbsp;</td>
                    <td class="col-xs-3">&nbsp;</td>
                <?php endif; endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<?= $this->element('CsvMigrations.associated_records'); ?>