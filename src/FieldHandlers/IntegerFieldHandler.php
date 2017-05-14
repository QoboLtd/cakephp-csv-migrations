<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseNumberFieldHandler;

class IntegerFieldHandler extends BaseNumberFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'integer';

    /**
     * Renderer to use
     */
    const RENDERER = 'integer';
}
