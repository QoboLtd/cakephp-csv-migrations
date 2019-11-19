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

use Cake\Core\Configure;

/**
 * CurrencyRenderer
 *
 * Render value as list item
 */
class CurrencyRenderer extends ListRenderer
{
    /**
     * Icon html
     */
    const ICON_HTML = '<span title="%s">%s&nbsp;(%s)</span>';

    /**
     * Provide rendered value
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $result = parent::provide($data, $options);
        $errorResponse = sprintf(parent::VALUE_NOT_FOUND_HTML, $data);

        //In case the result is the same with the errorResponse then return the error
        if (empty($result) || $errorResponse == $result) {
            return $result;
        } else {
            return static::getIcon($data, $result);
        }
    }

    /**
     * Get Icon html
     *
     * @param      string  $key    The key
     * @param      string  $value  The value
     *
     * @return     string  The icon.
     */
    public static function getIcon(string $key, string $value): string
    {
        $currenciesList = Configure::readOrFail('Currencies.list');
        //Check if the key exist in currencies list else return just the value
        if (!array_key_exists($key, $currenciesList)) {
            return $value;
        }

        return sprintf(static::ICON_HTML, $currenciesList[$key]['description'], $currenciesList[$key]['symbol'], $value);
    }
}
