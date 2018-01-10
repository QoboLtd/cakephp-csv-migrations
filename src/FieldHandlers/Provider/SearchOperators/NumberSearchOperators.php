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
namespace CsvMigrations\FieldHandlers\Provider\SearchOperators;

/**
 * NumberSearchOperators
 *
 * Search operators for numeric values
 */
class NumberSearchOperators extends AbstractSearchOperators
{
    /**
     * @var array $operators Search operators
     */
    protected $operators = [
        'is' => [
            'label' => 'is',
            'operator' => 'IN',
            'emptyCriteria' => [
                'aggregator' => 'OR',
                'values' => ['IS NULL', '= 0']
            ]
        ],
        'is_not' => [
            'label' => 'is not',
            'operator' => 'NOT IN',
            'emptyCriteria' => [
                'aggregator' => 'AND',
                'values' => ['IS NOT NULL', '!= 0']
            ]
        ],
        'greater' => [
            'label' => 'greater',
            'operator' => '>',
            'emptyCriteria' => [
                'aggregator' => 'AND',
                'values' => ['IS NOT NULL', '> 0']
            ]
        ],
        'less' => [
            'label' => 'less',
            'operator' => '<',
            'emptyCriteria' => [
                'aggregator' => 'AND',
                'values' => ['IS NOT NULL', '< 0']
            ]
        ],
    ];
}
