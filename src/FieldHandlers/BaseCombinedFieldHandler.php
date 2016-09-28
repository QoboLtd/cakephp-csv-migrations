<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\ListFieldHandler;

abstract class BaseCombinedFieldHandler extends ListFieldHandler
{
    /**
     * Combined fields
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->_setCombinedFields();
    }

    /**
     * Set combined fields
     *
     * @return void
     */
    abstract protected function _setCombinedFields();

    /**
     * {@inheritDoc}
     *
     * @todo refactor to use base fields as renderValue() does now
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $input = $this->cakeView->Form->label($field);

        $input .= '<div class="row">';
        foreach ($this->_fields as $suffix => $preOptions) {
            $fieldName = $field . '_' . $suffix;
            if (isset($options['entity'])) {
                $data = $options['entity']->{$fieldName};
            }
            $fullFieldName = $this->_getFieldName($table, $fieldName, $preOptions);

            $fieldOptions = [
                'label' => false,
                'type' => $preOptions['type'],
                'required' => (bool)$options['fieldDefinitions']->getRequired(),
                'escape' => false,
                'value' => $data
            ];

            if (array_key_exists($fieldOptions['type'], $this->_fieldTypes)) {
                $fieldOptions['type'] = $this->_fieldTypes[$fieldOptions['type']];
            }

            $input .= '<div class="';
            switch ($preOptions['field']) {
                case 'select':
                    $input .= 'col-xs-6 col-sm-4 col-sm-offset-2">';
                    $selectOptions = $this->_getSelectOptions($options['fieldDefinitions']->getLimit());
                    $input .= $this->cakeView->Form->select($fullFieldName, $selectOptions, $fieldOptions);
                    break;

                case 'input':
                    $input .= 'col-xs-6">';
                    $input .= $this->cakeView->Form->input($fullFieldName, $fieldOptions);
                    break;
            }
            $input .= '</div>';
        }
        $input .= '</div>';

        return $input;
    }

    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = [];
        foreach ($this->_fields as $suffix => $fieldOptions) {
            $fieldName = $field . '_' . $suffix;
            if (isset($options['entity'])) {
                $data = $options['entity']->{$fieldName};
            }
            $handler = new $fieldOptions['handler'];
            $result[] = $handler->renderValue($table, $fieldName, $data, $options);
        }

        $result = implode('&nbsp;', $result);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function fieldToDb(CsvField $csvField)
    {
        foreach ($this->_fields as $suffix => $options) {
            $dbFields[] = new DbField(
                $csvField->getName() . '_' . $suffix,
                $options['type'],
                null,
                $csvField->getRequired(),
                $csvField->getNonSearchable(),
                $csvField->getUnique()
            );
        }

        return $dbFields;
    }
}
