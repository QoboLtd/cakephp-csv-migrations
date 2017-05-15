<?php
namespace CsvMigrations\FieldHandlers\Renderer;

use InvalidArgumentException;

/**
 * DateTimeRenderer
 *
 * Render value as date time
 */
class DateTimeRenderer extends BaseRenderer
{
    /**
     * Date/time format
     */
    const FORMAT = 'yyyy-MM-dd HH:mm';

    /**
     * Render value
     *
     * Supported options:
     *
     * * format - date time format as expecte by Date/Time classes.
     *            Default: 'yyyy-MM-dd HH:mm'.
     *
     * NOTE: Formatting will only applied to object values, not strings.
     *
     * @throws \InvalidArgumentException when sanitize fails
     * @param mixed $value Value to render
     * @param array $options Rendering options
     * @return string Text, HTML or other string result
     */
    public function renderValue($value, array $options = [])
    {
        $result = '';

        if (!isset($options['format'])) {
            $options['format'] = static::FORMAT;
        }

        // Format object timestamp
        if (is_object($value)) {
            if (method_exists($value, 'i18nFormat') && is_callable([$value, 'i18nFormat'])) {
                $value = $value->i18nFormat($options['format']);
            } else {
                throw new InvalidArgumentException("Failed to sanitize timestamp");
            }
        }

        $value = (string)$value;
        if (empty($value)) {
            return $result;
        }

        $result = parent::renderValue($value, $options);

        return $result;
    }
}
