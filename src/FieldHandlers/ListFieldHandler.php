<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;
use CsvMigrations\ListTrait;

class ListFieldHandler extends BaseFieldHandler
{
    use ListTrait;

    /**
     * Field type
     */
    const FIELD_TYPE = 'string';

    /**
     * Field type match pattern
     */
    const FIELD_TYPE_PATTERN = '/list\((.*?)\)/';

    /**
     * Input field html markup
     */
    const INPUT_HTML = '<div class="form-group">%s</div>';

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
        $fieldOptions = $this->_getSelectOptions($options['fieldDefinitions']->getLimit());

        $input = $this->cakeView->Form->label($field);
        $input .= $this->cakeView->Form->select($this->_getFieldName($table, $field, $options), $fieldOptions, [
            'class' => 'form-control',
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
            'value' => $data
        ]);

        return sprintf(static::INPUT_HTML, $input);
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
        $result = '';

        $fieldOptions = $this->_getSelectOptions($options['fieldDefinitions']->getLimit());

        if (isset($fieldOptions[$data])) {
            // Concatenate all parents together with value
            $parents = explode('.', $data);
            if (!empty($parents)) {
                $path = '';
                foreach ($parents as $parent) {
                    $path = empty($path) ? $parent : $path . '.' . $parent;
                    if (isset($fieldOptions[$path])) {
                        $result .= $fieldOptions[$path];
                    }
                }
            }
        } else {
            $result = $data;
        }

        return $result;
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
            static::FIELD_TYPE,
            null,
            $csvField->getRequired(),
            $csvField->getNonSearchable(),
            $csvField->getUnique()
        );

        return $dbFields;
    }
}
