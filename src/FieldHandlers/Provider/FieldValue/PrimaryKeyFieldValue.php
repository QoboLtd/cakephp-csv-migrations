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
 * PrimaryKeyFieldValue
 *
 * MixedFieldValue provides the functionality
 * of looking for field value in a variety of
 * (mixed) data.
 */
class PrimaryKeyFieldValue extends MixedFieldValue
{
    /**
     * Provide field value
     *
     * @param mixed $data Data to look for field value in (Request, Entity, etc)
     * @param array $options Options to use for provision
     * @return mixed Field value
     */
    public function provide($data = null, array $options = [])
    {
        $primaryKey = $this->config->getTable()->getPrimaryKey();
        $currentField = $this->config->getField();

        // temporarily switch field to primary key
        $this->config->setField($primaryKey);

        $result = parent::provide($data, $options);

        // reset field back to the current field
        $this->config->setField($currentField);

        return $result;
    }
}
