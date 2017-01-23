<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseSimpleFieldHandler;

class PhoneFieldHandler extends BaseSimpleFieldHandler
{
    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'tel';
}
