<?php
$options = array(
    'entity' => $entity,
    'fields' => $fields
);
echo $this->element('CsvMigrations.View/add', [
    'options' => $options
]);