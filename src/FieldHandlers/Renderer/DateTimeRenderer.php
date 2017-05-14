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
     * @throws \InvalidArgumentException when sanitize fails
     * @param mixed $value Value to render
     * @param array $options Rendering options
     * @return string Text, HTML or other string result
     */
    public function renderValue($value, array $options = [])
    {
        if (is_object($value)) {
            if (method_exists($value, 'i18nFormat') && is_callable([$value, 'i18nFormat'])) {
                $value = $value->i18nFormat(static::FORMAT);
            } else {
                throw new InvalidArgumentException("Failed to sanitize timestamp");
            }
        }

        $result = parent::renderValue($value, $options);

        return $result;
    }
}
