<?php
namespace CsvMigrations\FieldHandlers\Renderer;

/**
 * DateRenderer
 *
 * Render value as date
 */
class DateRenderer extends DateTimeRenderer
{
    /**
     * Date/time format
     */
    const FORMAT = 'yyyy-MM-dd';
}
