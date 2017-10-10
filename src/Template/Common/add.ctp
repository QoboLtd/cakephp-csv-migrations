<?php
use Cake\Utility\Inflector;

// Get title from the entity
$title = Inflector::singularize(Inflector::humanize(Inflector::underscore($moduleAlias)));

$options = [
    'entity' => $entity,
    'fields' => $fields,
    'title' => __('Create {0}', $title),
    'handlerOptions' => ['entity' => $this->request]
];
echo $this->element('CsvMigrations.View/post', ['options' => $options]);
