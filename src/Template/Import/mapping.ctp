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
use CsvMigrations\Utility\Import as ImportUtility;

$factory = new FieldHandlerFactory();

$tableName = $this->name;
if ($this->plugin) {
    $tableName = $this->plugin . '.' . $tableName;
}

$headerOptions = [];
foreach ($headers as $header) {
    $headerOptions = array_merge($headerOptions, [
        strtolower(trim($header)) => $header,
        Inflector::underscore(str_replace(' ', '', trim($header))) => $header
    ]);
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
    $options['title'] .= __d('Qobo/CsvMigrations', 'Import fields mapping');
}

$lang_field = ImportUtility::getTranslationFields($this->name, $headers);
$columns = array_merge(array_flip($columns), $lang_field);
ksort($columns);

$unique = ImportUtility::uniqueColumns($this->name);
$identifier = [];
foreach ($unique as $item) {
    $identifier[$item] = $factory->renderName($this->name, $item);
}

echo $this->element('CsvMigrations.common_js_libs', ['scriptBlock' => 'bottom']);
?>

<?php $this->Html->scriptStart(array('block' => 'scriptBottom', 'inline' => false)); ?>
;(function ($) {
  $('.form-control[data-class="select2"]')
    .select2({
      theme: 'bootstrap',
      width: '100%',
      placeholder: '-- Please choose --',
      escapeMarkup: function (text) {
        return text
      }
    })
    .val(null)
    .trigger('change')
})(jQuery)

$("#identifierRow").hide()
$('#is_update:checkbox').on('ifChanged', function(e){
    $("#identifierRow").toggle()
    $("#identifierRow").find("label").parent().toggleClass("required")
    $('select[name="options[options][update_identifier]"]').prop('required', this.checked)
    $('select[name="options[options][update_identifier]"]').val("").trigger("change")
});

$('select[name="options[options][update_identifier]"]').on("change", function(){
    if ($(this).val() === 'id') {
         $("<input />").attr("type", "hidden").attr("name", "options[fields][id][column]").attr("value", "id").appendTo("#mapping");
    }
});

<?php $this->Html->scriptEnd(); ?>

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
                <?php if(!empty($identifier)): ?>
                <?= $this->Form->create($import, ["id" => "mapping"]) ?>
                <div class="box-body">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-8">
                        <div class="form-group input checkbox">
                            <input type="checkbox" name="options[options][update]" value="1" class="square" id="is_update">
                            <label class="control-label"><?= __d('Qobo/CsvMigrations', 'Update existing records') ?></label>
                        </div>
                    </div>
                </div>
                <div class="row" id="identifierRow">
                    <div class="col-md-3">
                        <div class="visible-md visible-lg text-right">
                            <label class="control-label ">
                                <?= __d('Qobo/CsvMigrations', 'Match records by') ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <?= $this->Form->control('options.options.update_identifier', [
                            'empty' => true,
                            'label' => false,
                            'type' => 'select',
                            'value' => $identifier,
                            'options' => $identifier,
                            'class' => 'form-control select2'
                        ]) ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="visible-md visible-lg text-center">
                    <div class="row">
                        <div class="col-md-3"><h4><?= __d('Qobo/CsvMigrations', 'Field') ?></h4></div>
                        <div class="col-md-4"><h4><?= __d('Qobo/CsvMigrations', 'File Column') ?></h4></div>
                        <div class="col-md-4"><h4><?= __d('Qobo/CsvMigrations', 'Default Value') ?></h4></div>
                    </div>
                </div>
                <?php foreach ($columns as $column => $detail) : ?>
                    <?php
                    $searchOptions = $factory->getSearchOptions($this->name, $column, [
                        'multiple' => false, // disable multi-selection
                        'magic-value' => false // disable magic values
                    ]);

                    // skip fields with no input markup
                    if (! isset($searchOptions[$column]['input']['content'])) {
                        continue;
                    }
                    is_numeric($detail) ?
                    $label = $factory->renderName($this->name, $column):
                    $label = $factory->renderName($this->name, $detail['parent']) . " (". strtoupper($detail['lang']) .")";

                    ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="visible-md visible-lg text-right">
                                <?= $this->Form->label($column, $label) ?>
                            </div>
                            <div class="visible-xs visible-sm">
                                <?= $this->Form->label($column, $label) ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <?php
                            $selected = false;
                            $selected = array_key_exists(strtolower($label), $headerOptions) ? $headerOptions[strtolower($label)] : $selected;
                            $selected = array_key_exists($column, $headerOptions) ? $headerOptions[$column] : $selected;
                            ?>
                            <?= $this->Form->control('options.fields.' . $column . '.column', [
                                'empty' => true,
                                'label' => false,
                                'type' => 'select',
                                'value' => $selected,
                                'options' => array_combine($headers, $headers),
                                'class' => 'form-control select2'
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= str_replace(
                                ['{{name}}', '{{value}}'],
                                [sprintf('options[fields][%s][default]', $column), ''],
                                $searchOptions[$column]['input']['content']
                            ) ?>
                        </div>
                    </div>
                <?php endforeach ?>
                <?= $this->Form->button(__d('Qobo/CsvMigrations', 'Submit'), ['type' => 'submit', 'class' => 'btn btn-primary']) ?>
                <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</section>
