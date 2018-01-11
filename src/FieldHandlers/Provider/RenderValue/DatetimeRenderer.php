<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CsvMigrations\FieldHandlers\Provider\RenderValue;

use InvalidArgumentException;

/**
 * DatetimeRenderer
 *
 * Render value as date time
 */
class DatetimeRenderer extends AbstractRenderer
{
    /**
     * Date/time format
     */
    const FORMAT = 'yyyy-MM-dd HH:mm';

    /**
     * Provide rendered value
     *
     * Supported options:
     *
     * * format - date time format as expecte by Date/Time classes.
     *            Default: 'yyyy-MM-dd HH:mm'.
     *
     * NOTE: Formatting will only applied to object values, not strings.
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $result = '';

        if (!isset($options['format'])) {
            $options['format'] = static::FORMAT;
        }

        // Format object timestamp
        if (is_object($data)) {
            if (method_exists($data, 'i18nFormat') && is_callable([$data, 'i18nFormat'])) {
                $data = $data->i18nFormat($options['format']);
            } else {
                throw new InvalidArgumentException("Failed to sanitize timestamp");
            }
        }

        $data = (string)$data;
        if (empty($data)) {
            return $result;
        }

        $result = parent::provide($data, $options);

        return $result;
    }
}
