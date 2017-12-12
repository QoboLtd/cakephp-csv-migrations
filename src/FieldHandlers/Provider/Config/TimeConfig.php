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

/**
 * TimeConfig
 *
 * This class provides the predefined configuration
 * for time field handlers.
 */
class TimeConfig extends FixedConfig
{
    /**
     * @var array $config Field handler configuration
     */
    protected $config = [
        'searchOperators' => '\\CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\TimeSearchOperators',
        'valueRenderAs' => '\\CsvMigrations\\FieldHandlers\\Renderer\\Value\\TimeRenderer',
    ];
}