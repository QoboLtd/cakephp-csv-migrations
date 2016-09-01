<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;
use Phinx\Db\Adapter\MysqlAdapter;

class TextFieldHandler extends BaseFieldHandler
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
            MysqlAdapter::TEXT_LONG,
            $csvField->getRequired(),
            $csvField->getNonSearchable(),
            $csvField->getUnique()
        );

        return $dbFields;
    }
}
