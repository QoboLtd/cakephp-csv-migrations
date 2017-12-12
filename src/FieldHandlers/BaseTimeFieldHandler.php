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
namespace CsvMigrations\FieldHandlers;

use Cake\I18n\Date;
use Cake\I18n\Time;
use CsvMigrations\FieldHandlers\BaseStringFieldHandler;

/**
 * BaseTimeFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to date and time field handlers.
 */
abstract class BaseTimeFieldHandler extends BaseStringFieldHandler
{
    /**
     * Renderer to use
     */
    const RENDERER = 'dateTime';

    /**
     * @var string $defaultConfigClass Config class to use as default
     */
    protected $defaultConfigClass = '\\CsvMigrations\\FieldHandlers\\Provider\\Config\\TimeConfig';

    /**
     * Render field input
     *
     * This method prepares the form input for the given field,
     * including the input itself, label, pre-populated value,
     * and so on.  The result can be controlled via the variety
     * of options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field input HTML
     */
    public function renderInput($data = '', array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $data = $this->_getFieldValueFromData($data);
        if (empty($data) && !empty($options['default'])) {
            $data = $options['default'];
        }

        if ($data instanceof Time) {
            $data = $data->i18nFormat(static::FORMAT);
        }
        if ($data instanceof Date) {
            $data = $data->i18nFormat(static::FORMAT);
        }

        return parent::renderInput($data, $options);
    }
}
