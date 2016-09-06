<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class DblistFieldHandler extends BaseFieldHandler
{
    /**
     * Field type
     */
    const DB_FIELD_TYPE = 'string';

    /**
     * Method responsible for rendering field's input.
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field input
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $result = '';
        //CsvField object is mandatory
        if (!isset($options['fieldDefinitions']) ||
            !($options['fieldDefinitions'] instanceof CsvField)) {
            return $result;
        }
        $csvObj = $options['fieldDefinitions'];
        $list = $csvObj->getListName();
        $field = $this->_getFieldName($table, $field, $options);
        $options = [
            'class' => 'form-control',
            'value' => $data,
            'required' => $csvObj->getRequired(),
        ];
        $result = $this->cakeView->cell('CsvMigrations.Dblist::' . __FUNCTION__, [$field, $list, $options])->render(__FUNCTION__);

        return $result;
    }

    /**
     * Method that renders list field's value.
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        //CsvField object is mandatory
        if (!isset($options['fieldDefinitions']) ||
            !($options['fieldDefinitions'] instanceof CsvField)) {
            return $result;
        }
        $csvObj = $options['fieldDefinitions'];
        $list = $csvObj->getListName();

        return $this->cakeView->cell('CsvMigrations.Dblist::' . __FUNCTION__, [$data, $list])->render(__FUNCTION__);
    }

    /**
     * Method responsible for converting csv field instance to database field instance.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array list of DbField instances
     */
    public function fieldToDb(CsvField $csvField)
    {
        $dbFields[] = new DbField(
            $csvField->getName(),
            static::DB_FIELD_TYPE,
            null,
            $csvField->getRequired(),
            $csvField->getNonSearchable(),
            $csvField->getUnique()
        );

        return $dbFields;
    }
}
