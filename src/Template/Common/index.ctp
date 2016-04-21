<?php
$options = array(
    'entities' => $entities,
    'fields' => $fields
);
echo $this->element('CsvMigrations.View/index', [
    'options' => $options
]);
