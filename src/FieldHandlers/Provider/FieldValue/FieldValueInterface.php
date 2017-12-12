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
namespace CsvMigrations\FieldHandlers\Provider\FieldValue;

/**
 * FieldValueInterface
 *
 * FieldValueInterface defines the contract that all
 * field value providers have to implement.
 */
interface FieldValueInterface
{
    /**
     * Get field value
     *
     * @param mixed $data Data to look for field value in (Request, Entity, array, etc)
     * @param string $field Field name
     * @return mixed Field value
     */
    public function provide($data, $field);
}
