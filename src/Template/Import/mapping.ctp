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

use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

$factory = new FieldHandlerFactory();

$tableName = $this->name;
if ($this->plugin) {
    $tableName = $this->plugin . '.' . $tableName;
}

$headerOptions = [];
foreach ($headers as $header) {
    $key = Inflector::underscore(str_replace(' ', '', trim($header)));
    $headerOptions[$key] = $header;
}

$options = [
    'title' => null,
    'entity' => null,
    'fields' => [],
];

// generate title
if (!$options['title']) {
    $config = (new ModuleConfig(ConfigType::MODULE(), $this->name))->parse();
    $options['title'] = $this->Html->link(
        isset($config->table->alias) ? $config->table->alias : Inflector::humanize(Inflector::underscore($this->name)),
        ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'index']
    );
    $options['title'] .= ' &raquo; ';
    $options['title'] .= __('Import fields mapping');
}

echo $this->element('CsvMigrations.common_js_libs', ['scriptBlock' => 'bottom']);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= $options['title'] ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-10 col-lg-8">
            <div class="box box-primary">
                <div class="box-body">
                <?= $this->Form->create($import) ?>
                <div class="visible-md visible-lg text-center">
                    <div class="row">
                        <div class="col-md-3"><h4><?= __('Field') ?></h4></div>
                        <div class="col-md-4"><h4><?= __('File Column') ?></h4></div>
                        <div class="col-md-4"><h4><?= __('Default Value') ?></h4></div>
                    </div>
                </div>
                <?php foreach ($columns as $column) : ?>
                    <?php $label = $factory->renderName($this->name, $column) ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="visible-md visible-lg text-right">
                                <?= $this->Form->label($label) ?>
                            </div>
                            <div class="visible-xs visible-sm">
                                <?= $this->Form->label($label) ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <?= $this->Form->input('options.fields.' . $column . '.column', [
                                'empty' => true,
                                'label' => false,
                                'type' => 'select',
                                'value' => array_key_exists($column, $headerOptions) ? $headerOptions[$column] : false,
                                'options' => array_combine($headers, $headers),
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                            $searchOptions = $factory->getSearchOptions($this->name, $column, [
                                'multiple' => false, // disable multi-selection
                                'magic-value' => false // disable magic values
                            ]);

                            if (isset($searchOptions[$column]['input']['content'])) {
                                echo str_replace(
                                    ['{{name}}', '{{value}}'],
                                    [sprintf('options[fields][%s][default]', $column), ''],
                                    $searchOptions[$column]['input']['content']
                                );
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach ?>
                <?= $this->Form->button(__('Submit'), ['type' => 'submit', 'class' => 'btn btn-primary']) ?>
                <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</section>
