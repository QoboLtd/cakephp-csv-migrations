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

use CsvMigrations\FieldHandlers\BaseStringFieldHandler;

class EmailFieldHandler extends BaseStringFieldHandler
{
    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'email';

    /**
     * @var string $defaultConfigClass Config class to use as default
     */
    protected $defaultConfigClass = '\\CsvMigrations\\FieldHandlers\\Provider\\Config\\EmailConfig';
}
