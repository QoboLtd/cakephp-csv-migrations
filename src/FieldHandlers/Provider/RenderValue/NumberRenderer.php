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
 * NumberRenderer
 *
 * Render value as number
 */
class NumberRenderer extends AbstractRenderer
{
    /**
     * Decimal precision
     */
    const PRECISION = 2;

    /**
     * Provide rendered value
     *
     * Supported options:
     *
     * * precision - integer value as to how many decimal points to render.
     *               Default: 2.
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        // Sanitize
        $result = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if ($result === false) {
            throw new InvalidArgumentException("Failed to sanitize number");
        }

        $result = (float)$result;

        if (!isset($options['precision'])) {
            $options['precision'] = static::PRECISION;
        }

        if (!empty($result) && is_numeric($result)) {
            $result = number_format($result, $options['precision']);
        } else {
            $result = (string)$result;
        }

        return $result;
    }
}
