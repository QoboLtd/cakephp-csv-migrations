<?php
$options = [
    'fields' => $fields
];
echo $this->element('CsvMigrations.View/index', [
    'options' => $options
]);
