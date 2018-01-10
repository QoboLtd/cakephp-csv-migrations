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
 * StringSearchOperators
 *
 * Search operators for string values
 */
class StringSearchOperators extends AbstractSearchOperators
{
    /**
     * @var array $operators Search operators
     */
    protected $operators = [
        'contains' => [
            'label' => 'contains',
            'operator' => 'LIKE',
            'pattern' => '%{{value}}%',
            'emptyCriteria' => [
                'aggregator' => 'OR',
                'values' => ['IS NULL', '= ""']
            ]
        ],
        'not_contains' => [
            'label' => 'does not contain',
            'operator' => 'NOT LIKE',
            'pattern' => '%{{value}}%',
            'emptyCriteria' => [
                'aggregator' => 'AND',
                'values' => ['IS NOT NULL', '!= ""']
            ]
        ],
        'starts_with' => [
            'label' => 'starts with',
            'operator' => 'LIKE',
            'pattern' => '{{value}}%',
            'emptyCriteria' => [
                'aggregator' => 'AND',
                'values' => ['IS NOT NULL', '!= ""']
            ]
        ],
        'ends_with' => [
            'label' => 'ends with',
            'operator' => 'LIKE',
            'pattern' => '%{{value}}',
            'emptyCriteria' => [
                'aggregator' => 'AND',
                'values' => ['IS NOT NULL', '!= ""']
            ]
        ],
    ];
}
