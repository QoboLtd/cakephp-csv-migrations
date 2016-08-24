<?php
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\BaseFileFieldHandler;
use CsvMigrations\FileUploadsUtils;

class FilesFieldHandler extends BaseFileFieldHandler
{
    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $table = TableRegistry::get($options['fieldDefinitions']->getLimit());

        return parent::renderValue($table, $field, $data, $options);
    }
}
