<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;
use Phinx\Db\Adapter\MysqlAdapter;

class BlobFieldHandler extends BaseFieldHandler
{
    /**
     * {@inheritDoc}
     * In addtion, it sets the limit to Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG
     */
    public function fieldToDb(CsvField $csvField)
    {
        $dbFields[] = new DbField(
            $csvField->getName(),
            $csvField->getType(),
            MysqlAdapter::BLOB_LONG,
            $csvField->getRequired(),
            $csvField->getNonSearchable(),
            $csvField->getUnique()
        );

        return $dbFields;
    }

    /**
     * Render value as-is
     *
     * @todo Add support for encoding (base64, etc) via $options
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = $data;

        return $result;
    }
}
