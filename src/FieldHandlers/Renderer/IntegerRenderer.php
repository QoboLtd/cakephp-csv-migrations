<?php
namespace CsvMigrations\FieldHandlers\Renderer;

/**
 * IntegerRenderer
 *
 * Integer renderer provides integer rendering functionality.
 */
class IntegerRenderer extends NumberRenderer
{
    /**
     * Precision
     *
     * Temporary setting for decimal precision, until
     * we learn to read it from the fields.ini.
     *
     * @todo Replace with configuration from fields.ini
     */
    const PRECISION = 0;
}
