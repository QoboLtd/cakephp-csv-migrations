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

class MetricFieldHandler extends BaseCombinedFieldHandler
{
    /**
     * Combined fields
     *
     * @var array
     */
    protected static $_fields = [
        'amount' => [
            'handler' => __NAMESPACE__ . '\\DecimalFieldHandler'
        ],
        'unit' => [
            'handler' => __NAMESPACE__ . '\\ListFieldHandler'
        ]
    ];
}
