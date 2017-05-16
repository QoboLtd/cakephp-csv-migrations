<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseStringFieldHandler;

class EmailFieldHandler extends BaseStringFieldHandler
{
    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'email';

    /**
     * Renderer to use
     */
    const RENDERER = 'email';
}
