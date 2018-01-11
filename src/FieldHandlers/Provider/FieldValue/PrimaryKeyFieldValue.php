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

use Cake\Database\Exception;
use CsvMigrations\FieldHandlers\Provider\AbstractProvider;

/**
 * PrimaryKeyFieldValue
 *
 * PrimaryKeyFieldValue provides the functionality
 * of looking for field value in entity's primary key.
 */
class PrimaryKeyFieldValue extends AbstractProvider
{
    /**
     * Provide field value
     *
     *
     * @param mixed $data Field value (Request, Entity, etc)
     * @param array $options Options to use for provision
     * @return mixed Field value
     */
    public function provide($data = null, array $options = [])
    {
        if (empty($options['entity'])) {
            return null;
        }

        // return null in cases where no table or a dummy table was provided by the config class
        try {
            $primaryKey = $this->config->getTable()->getPrimaryKey();
        } catch (Exception $e) {
            return null;
        }

        return $options['entity']->get($primaryKey);
    }
}
