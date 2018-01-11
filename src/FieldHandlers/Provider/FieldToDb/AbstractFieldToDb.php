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
namespace CsvMigrations\FieldHandlers\Provider\FieldToDb;

use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\DbField;
use CsvMigrations\FieldHandlers\Provider\AbstractProvider;
use InvalidArgumentException;

/**
 * AbstractFieldToDb
 *
 * Abstract FieldToDb provides the default conversion
 * functionality.
 */
abstract class AbstractFieldToDb extends AbstractProvider
{
    /**
     * @var string $dbFieldType Database field type
     */
    protected $dbFieldType = 'string';

    /**
     * Provide rendered value
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        if (!$data instanceof CsvField) {
            throw new InvalidArgumentException("Given data is not an instance of CsvField");
        }

        $data->setType($this->dbFieldType);

        $dbField = DbField::fromCsvField($data);
        $result = [
            $data->getName() => $dbField,
        ];

        return $result;
    }
}
