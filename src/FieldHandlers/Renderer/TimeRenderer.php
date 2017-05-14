<?php
namespace CsvMigrations\FieldHandlers\Renderer;

/**
 * TimeRenderer
 *
 * Render value as time
 */
class TimeRenderer extends DateTimeRenderer
{
    /**
     * Date/time format
     */
    const FORMAT = 'HH:mm';
}
