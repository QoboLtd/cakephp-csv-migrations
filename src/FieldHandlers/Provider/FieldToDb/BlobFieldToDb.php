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
use InvalidArgumentException;
use Phinx\Db\Adapter\MysqlAdapter;

/**
 * BlobFieldToDb
 *
 * Blob FieldToDb provides the conversion functionality
 * for BLOB fields.
 */
class BlobFieldToDb extends AbstractFieldToDb
{
    /**
     * @var string $dbFieldType Database field type
     */
    protected $dbFieldType = 'blob';

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
        // Set the limit to Phinx\Db\Adapter\MysqlAdapter::BLOB_LONG
        $data->setLimit(MysqlAdapter::BLOB_LONG);

        $dbField = DbField::fromCsvField($data);
        $result = [
            $data->getName() => $dbField,
        ];

        return $result;
    }
}
