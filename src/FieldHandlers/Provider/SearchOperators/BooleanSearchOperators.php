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
 * BooleanSearchOperators
 *
 * Search operators for boolean values
 */
class BooleanSearchOperators extends AbstractSearchOperators
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
                'values' => ['IS NULL', '= ""', '= 0', '= false']
            ]
        ],
        'is_not' => [
            'label' => 'is not',
            'operator' => 'NOT IN',
            'emptyCriteria' => [
                'aggregator' => 'AND',
                'values' => ['IS NOT NULL', '!= ""', '!= 0', '!= false']
            ]
        ],
    ];
}
