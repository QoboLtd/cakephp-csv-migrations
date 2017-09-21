<?php
use Cake\Utility\Inflector;

$title = __('Batch edit {0}', strtolower(Inflector::humanize(Inflector::underscore($this->name))));

$options = [
    'title' => $title,
    'entity' => $entity,
    'fields' => $fields,
    'handlerOptions' => [
        'attributes' => [
            'data-batch' => 'field',
            'disabled' => true
        ]
    ]
];
echo $this->element('CsvMigrations.View/add', [
    'options' => $options,
]);

echo $this->Html->script('CsvMigrations.view-batch', ['block' => 'scriptBottom']);

$formUrl = $this->Url->build([
    'plugin' => $this->request->plugin,
    'controller' => $this->request->controller,
    'action' => $this->request->action
]);
echo $this->Html->scriptBlock(
    '$("form[action=\'' . $formUrl . '\']").viewBatch({
        batch_ids: ' . json_encode($this->request->data('batch.ids')) . ',
        target_id: "*[data-batch=\'field\']",
        disable_id: "*[data-batch=\'disable\']",
        enable_id: "*[data-batch=\'enable\']",
        wrapper_id: ".field-wrapper"
    })',
    ['block' => 'scriptBottom']
);
