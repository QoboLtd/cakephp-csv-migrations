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

namespace CsvMigrations\FieldHandlers\Provider\DbFieldType;

use CsvMigrations\FieldHandlers\Provider\AbstractProvider;

/**
 * AbstractDbFieldType
 *
 * Abstract DbFieldType provides the default functionality
 * for fetching the field's database type.
 */
abstract class AbstractDbFieldType extends AbstractProvider
{
    /**
     * @var string $dbFieldType Database field type
     */
    protected $dbFieldType = 'string';

    /**
     * Provide database field type
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return string
     */
    public function provide($data = null, array $options = [])
    {
        return $this->dbFieldType;
    }
}
