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

use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\Event\EventName;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

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

// get display field
$displayField = $table->getDisplayField();

$handlerOptions = ['entity' => $options['entity']];

// append to title
$options['title'] .= ' &raquo; ';
$displayValue = $options['entity']->get($displayField);
$options['title'] .= $factory->renderValue($tableName, $displayField, $displayValue, $handlerOptions);

if (!$this->request->query('embedded')) : ?>
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
                            <?= $this->element('CsvMigrations.Field/value', [
                                'factory' => $factory, 'field' => $field, 'options' => $options, 'user' => $user
                            ]) ?>
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
<?php if (!$this->request->query('embedded')) : ?>
    <?= $this->element('CsvMigrations.common_js_libs'); // loading common setup for typeahead/panel/etc libs ?>
    <hr />
    <div class="row associated-records">
        <div class="col-xs-12">
            <?= $this->element('CsvMigrations.View/associated', [
                'user' => $user, 'options' => $options, 'table' => $table
            ]) ?>
        </div>
    </div>
</section>
<?php endif; ?>