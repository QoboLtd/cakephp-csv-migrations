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

use Cake\Network\Request;
use Cake\ORM\Entity;

/**
 * MixedFieldValue
 *
 * MixedFieldValue provides the functionality
 * of looking for field value in a variety of
 * (mixed) data.
 */
class MixedFieldValue implements FieldValueInterface
{
    /**
     * Get field value
     *
     * @param mixed $data Data to look for field value in (Request, Entity, array, etc)
     * @param string $field Field name
     * @return mixed Field value
     */
    public function provide($data, $field)
    {
        // Use data as is
        $result = $data;

        // Use $data->$field if available as Entity
        if ($data instanceof Entity) {
            $result = null;
            if (isset($data->$field)) {
                $result = $data->$field;
            }

            return $result;
        }

        // Use $data->data[$field] if available as Request
        if ($data instanceof Request) {
            $result = null;
            if (is_array($data->data) && array_key_exists($field, $data->data)) {
                $result = $data->data[$field];
            }

            return $result;
        }

        return $result;
    }
}
