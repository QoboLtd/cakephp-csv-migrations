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
 * DecimalFieldToDb
 *
 * Decimal FieldToDb provides the conversion functionality
 * for decimal fields.
 */
class DecimalFieldToDb extends AbstractFieldToDb
{
    /**
     * @var string $dbFieldType Database field type
     */
    protected $dbFieldType = 'decimal';

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
        $limit = $dbField->getLimit();
        if (!empty($limit) && false !== strpos($limit, '.')) {
            list($precision, $scale) = explode('.', $limit);
            $fieldOptions = $dbField->getOptions();
            $fieldOptions['precision'] = $precision;
            $fieldOptions['scale'] = $scale;
            $dbField->setOptions($fieldOptions);
        }

        $result = [
            $data->getName() => $dbField,
        ];

        return $result;
    }
}
