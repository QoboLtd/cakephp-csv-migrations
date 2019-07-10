<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
deprecationWarning('"CsvMigrations.View/view" view is deprecated.');

use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

$factory = new FieldHandlerFactory($this);

$defaultOptions = [
    'title' => null,
    'entity' => null,
    'fields' => [],
];
if (empty($options)) {
    $options = [];
}
$options = array_merge($defaultOptions, $options);

// get table name
$tableName = $this->name;
if (!empty($this->plugin)) {
    $tableName = $this->plugin . '.' . $tableName;
}

// get table instance
$table = TableRegistry::get($tableName);

// generate title
if (!$options['title']) {
    $config = (new ModuleConfig(ConfigType::MODULE(), $this->name))->parse();
    $options['title'] = $this->Html->link(
        isset($config->table->alias) ? $config->table->alias : Inflector::humanize(Inflector::underscore($this->name)),
        ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'index']
    );
    $options['title'] .= ' &raquo; ';
    $options['title'] .= $factory->renderValue(
        $table,
        $table->getDisplayField(),
        $options['entity']->get($table->getDisplayField()),
        ['entity' => $options['entity']]
    );
}

if (!$this->request->getQuery('embedded')) : ?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= $options['title'] ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
            <div class="btn-group btn-group-sm" role="group">
                <?= $this->element('CsvMigrations.Menu/view_top', [
                    'options' => $options, 'displayField' => $table->getDisplayField()
                ]); ?>
            </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
<?php endif; ?>
<?php
// row field count with most fields
$fieldCountMax = 1;
foreach ($options['fields'] as $panelFields) {
    foreach ($panelFields as $subFields) {
        if (count($subFields) > $fieldCountMax) {
            $fieldCountMax = count($subFields);
        }
    }
}

$embeddedFields = [];
foreach ($options['fields'] as $panelName => $panelFields) : ?>
    <?php
    if ($this->request->getQuery('embedded')) {
        $panelName = Inflector::singularize(Inflector::humanize($this->name)) . ': ' . $panelName;
    }
    ?>
    <div class="box box-primary">
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
                <?php $fieldCount = 12 < count($subFields) ? 12 : count($subFields); ?>
                <?php if ('' === trim($field['name'])) : ?>
                    <div class="col-xs-4 col-md-2 text-right">&nbsp;</div>
                    <div class="col-xs-8 col-md-4">&nbsp;</div>
                    <?php continue; ?>
                <?php endif; ?>
                <?php
                // embedded field detection
                preg_match(CsvField::PATTERN_TYPE, $field['name'], $matches);

                if (! empty($matches[1]) && 'EMBEDDED' === $matches[1]) {
                    $embeddedFields[] = $matches[2];
                    continue;
                }

                echo $this->element('CsvMigrations.Field/value', [
                    'factory' => $factory,
                    'field' => $field,
                    'options' => $options,
                    'fieldCount' => $fieldCount,
                    'fieldCountMax' => $fieldCountMax
                ]);
                ?>
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

    echo $this->element('CsvMigrations.Embedded/fields', [
        'fields' => $embeddedFields, 'table' => $table, 'options' => $options
    ]);

    $embeddedFields = [];
    ?>
<?php endforeach; ?>
<?php if (!$this->request->getQuery('embedded')) : ?>
    <?= $this->element('CsvMigrations.common_js_libs'); // loading common setup for typeahead/panel/etc libs ?>
    <?= $this->Html->script('Qobo/Utils.dataTables.init', ['block' => 'scriptBottom']) ?>
    <hr />
    <div class="row associated-records">
        <div class="col-xs-12">
            <?= $this->element('CsvMigrations.View/associated', [
                'options' => $options, 'table' => $table, 'factory' => $factory, 'entity' => $options['entity']
            ]) ?>
        </div>
    </div>
</section>
<?php endif; ?>
