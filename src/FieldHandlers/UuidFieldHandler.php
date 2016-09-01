<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class UuidFieldHandler extends BaseFieldHandler
{
    /**
     * Field type
     */
    const DB_FIELD_TYPE = 'uuid';
}
