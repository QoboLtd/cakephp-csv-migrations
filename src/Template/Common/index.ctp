<?php
$options = array(
    'fields' => $fields
);
echo $this->element('CsvMigrations.View/index', [
    'options' => $options
]);
