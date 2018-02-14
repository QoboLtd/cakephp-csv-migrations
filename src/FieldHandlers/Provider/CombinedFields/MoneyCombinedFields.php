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
namespace CsvMigrations\FieldHandlers\Provider\CombinedFields;

/**
 * MoneyCombinedFields
 *
 * Money CombinedFields provides the functionality
 * for money combined fields configuration.
 */
class MoneyCombinedFields extends AbstractCombinedFields
{
    /**
     * @var array $fields List of fields
     */
    protected $fields = [
        'amount' => [
            'config' => 'CsvMigrations\\FieldHandlers\\Config\\DecimalConfig'
        ],
        'currency' => [
            'config' => 'CsvMigrations\\FieldHandlers\\Config\\ListConfig'
        ]
    ];
}
