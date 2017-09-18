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
echo $this->Html->scriptBlock('$(document).viewBatch()', ['block' => 'scriptBottom']);
