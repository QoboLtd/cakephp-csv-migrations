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
 * BaseSearchOperators
 *
 * Abstract base class implementing SearchOperatorsInterface
 */
abstract class BaseSearchOperators implements SearchOperatorsInterface
{
    /**
     * @var array $operators Search operators
     */
    protected $operators = [];

    /**
     * Get search operators
     *
     * @return array Search operators
     */
    public function provide()
    {
        return $this->operators;
    }
}
