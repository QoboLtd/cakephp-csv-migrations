<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseCsvListFieldHandler;

class ListFieldHandler extends BaseCsvListFieldHandler
{
    /**
     * Field type
     */
    const INPUT_FIELD_TYPE = 'select';

    /**
     * Render field input
     *
     * This method prepares the form input for the given field,
     * including the input itself, label, pre-populated value,
     * and so on.  The result can be controlled via the variety
     * of options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field input HTML
     */
    public function renderInput($data = '', array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $data = $this->_getFieldValueFromData($data);

        $fieldName = $this->table->aliasField($this->field);

        $params = [
            'field' => $this->field,
            'name' => $fieldName,
            'type' => static::INPUT_FIELD_TYPE,
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'options' => $this->_getSelectOptions($options['fieldDefinitions']->getLimit())
        ];

        return $this->_renderElement(__FUNCTION__, $params, $options);
    }
}
