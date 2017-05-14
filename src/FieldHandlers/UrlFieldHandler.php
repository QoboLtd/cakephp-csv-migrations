<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseStringFieldHandler;

class UrlFieldHandler extends BaseStringFieldHandler
{
    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'url';

    /**
     * Renderer to use
     */
    const RENDERER = 'url';
}
