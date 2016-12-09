<?php
$options = [
    'entity' => $entity,
    'fields' => $fields
];
echo $this->element('CsvMigrations.View/add', [
    'options' => $options
]);
