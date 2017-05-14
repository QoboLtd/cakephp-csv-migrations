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

    /**
     * Sanitize options
     *
     * Name of filter_var() filter to run and all desired
     * options/flags.
     *
     * @var array
     */
    public $sanitizeOptions = [FILTER_SANITIZE_NUMBER_INT];
}
