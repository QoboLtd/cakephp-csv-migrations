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

use MyCLabs\Enum\Enum;

/**
 * Field Handler Settings
 */
class Setting extends Enum
{
    /**
     * Label to use for the empty option in dropdown lists
     */
    const EMPTY_OPTION_LABEL = ' -- Please choose -- ';

    /**
     * Plain rendering to use for regular values
     */
    const RENDER_PLAIN_VALUE = 'plain';

    /**
     * Plain rendering to use for related (recursive) values
     */
    const RENDER_PLAIN_VALUE_RELATED = 'relatedPlain';
}
