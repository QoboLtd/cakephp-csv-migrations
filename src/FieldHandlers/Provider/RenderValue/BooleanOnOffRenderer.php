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

/**
 * BooleanOnOffRenderer
 *
 * Render boolean value as On or Off string.
 */
class BooleanOnOffRenderer extends BooleanRenderer
{
    /**
     * Provide rendered value
     *
     * Supported options:
     *
     * * valueLabels - array of two strings to use for labels.
     *                 Defaults: 'Off' for false, 'On' for true.
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $valueLabels = [
            0 => __('Off'),
            1 => __('On'),
        ];

        if (empty($options['valueLabels'])) {
            $options['valueLabels'] = $valueLabels;
        }
        $result = parent::provide($data, $options);

        return $result;
    }
}
