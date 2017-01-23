<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class PhoneFieldHandler extends BaseFieldHandler
{
    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'tel';
}
