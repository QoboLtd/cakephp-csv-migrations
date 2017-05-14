<?php
namespace CsvMigrations\FieldHandlers\Renderer;

/**
 * DecimalRenderer
 *
 * Decimal renderer provides decimal rendering functionality.
 */
class DecimalRenderer extends NumberRenderer
{
    /**
     * Precision
     *
     * Temporary setting for decimal precision, until
     * we learn to read it from the fields.ini.
     *
     * @todo Replace with configuration from fields.ini
     */
    const PRECISION = 2;
}
