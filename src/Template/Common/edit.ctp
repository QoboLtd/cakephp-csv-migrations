<?php
use Cake\Utility\Inflector;

// Get title from the entity
$title = Inflector::singularize(Inflector::humanize(Inflector::underscore($moduleAlias)));

$options = [
    'entity' => $entity,
    'fields' => $fields,
    'title' => __('Edit {0}', $title),
    'handlerOptions' => ['entity' => $entity]
];
echo $this->element('CsvMigrations.View/post', ['options' => $options]);
