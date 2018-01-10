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

use CsvMigrations\FieldHandlers\Provider\AbstractProvider;

/**
 * AbstractCombinedFields
 *
 * Abstract CombinedFields provides the default functionality
 * for combined fields provider.
 */
abstract class AbstractCombinedFields extends AbstractProvider
{
    /**
     * @var array $fields List of fields
     */
    protected $fields = [];

    /**
     * Provide rendered value
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        return $this->fields;
    }
}
