<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use CsvMigrations\FieldHandlers\BaseFieldHandler;
use Phinx\Db\Adapter\MysqlAdapter;

class TextFieldHandler extends BaseFieldHandler
{
    /**
     * Method responsible for converting csv field instance to database field instance.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return \CsvMigrations\FieldHandlers\DbField            DbField instance
     */
    public function fieldToDb(CsvField $csvField)
    {
        $dbField = new DbField(
            $csvField->getName(),
            $csvField->getType(),
            MysqlAdapter::TEXT_LONG,
            $csvField->getRequired(),
            $csvField->getNonSearchable()
        );

        return $dbField;
    }
}
