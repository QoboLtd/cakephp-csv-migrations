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
namespace CsvMigrations\FieldHandlers\Provider\Config;

use InvalidArgumentException;

/**
 * BooleanConfig
 *
 * This class provides the predefined configuration
 * for boolean field handlers.
 */
class BooleanConfig extends FixedConfig
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = [
            'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\BooleanSearchOperators',
            'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Renderer\\Value\\BooleanYesNoRenderer',
        ];
    }
}
