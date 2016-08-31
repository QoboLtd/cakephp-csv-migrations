<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;
use Phinx\Db\Adapter\MysqlAdapter;

class TextFieldHandler extends BaseFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = MysqlAdapter::TEXT_LONG;
}
