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
 * CountryRenderer
 *
 * Render value as list item
 */
class CountryRenderer extends ListRenderer
{
    /**
     * Icon html
     */
    const ICON_HTML = '<span class="flag-icon flag-icon-%s flag-icon-default"></span>&nbsp;&nbsp;%s';

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

        return sprintf(static::ICON_HTML, strtolower($data), $result);
    }
}
