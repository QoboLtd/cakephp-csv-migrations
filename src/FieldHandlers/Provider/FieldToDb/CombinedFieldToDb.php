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
 * CombinedFieldToDb
 *
 * Combined FieldToDb provides the conversion functionality
 * for combined fields.
 */
class CombinedFieldToDb extends AbstractFieldToDb
{
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

        $combinedFields = $this->config->getProvider('combinedFields');
        $combinedFields = new $combinedFields($this->config);
        $combinedFields = $combinedFields->provide($data, $options);

        $dbFields = [];
        foreach ($combinedFields as $suffix => $options) {
            $subField = clone $data;
            $subField->setName($data->getName() . '_' . $suffix);
            if (isset($options['limit'])) {
                $subField->setLimit($options['limit']);
            }

            $dbFields = array_merge($dbFields, $options['handler']::fieldToDb($subField));
        }

        return $dbFields;
    }
}
