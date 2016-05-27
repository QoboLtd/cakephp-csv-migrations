<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use CsvMigrations\FieldHandlers\ListFieldHandler;

class MoneyFieldHandler extends ListFieldHandler
{
    /**
     * {@inheritDoc}
     */
    const FIELD_TYPE_PATTERN = '/money\((.*?)\)/';

    /**
     * Money fields
     *
     * @var array
     */
    protected $_fields = [
        'currency' => [
            'type' => 'string',
            'field' => 'select'
        ],
        'amount' => [
            'type' => 'string',
            'field' => 'input'
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $cakeView = new AppView();

        $input = $cakeView->Form->label($field);

        $input .= '<div class="input-group">';
        foreach ($this->_fields as $suffix => $preOptions) {
            $fieldName = $field . '_' . $suffix;
            if (isset($options['entity'])) {
                $data = $options['entity']->{$fieldName};
            }
            $fullFieldName = $this->_getFieldName($table, $fieldName, $preOptions);

            $fieldOptions = [
                'label' => false,
                'type' => $preOptions['type'],
                'required' => (bool)$options['fieldDefinitions']['required'],
                'value' => $data
            ];

            $input .= '<div class="input-group-btn">';
            switch ($preOptions['field']) {
                case 'select':
                    $selectOptions = $this->_getSelectOptions($options['fieldDefinitions']['type']);
                    $input .= $cakeView->Form->select($fullFieldName, $selectOptions, $fieldOptions);
                    break;

                case 'input':
                    $input .= $cakeView->Form->input($fullFieldName, $fieldOptions);
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
        $result = '';
        foreach ($this->_fields as $suffix => $preOptions) {
            $fieldName = $field . '_' . $suffix;
            if (isset($options['entity'])) {
                $data = $options['entity']->{$fieldName};
            }
            $fullFieldName = $this->_getFieldName($table, $fieldName, $preOptions);

            $fieldOptions = [
                'label' => false,
                'type' => $preOptions['type'],
                'required' => (bool)$options['fieldDefinitions']['required'],
                'value' => $data
            ];

            switch ($preOptions['field']) {
                case 'select':
                    $selectOptions = $this->_getSelectOptions($options['fieldDefinitions']['type']);
                    $result .= h($selectOptions[$data]);
                    break;

                case 'input':
                    $result = h($data) . ' ' . $result;
                    break;
            }
        }

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
                $csvField->getNonSearchable()
            );
        }

        return $dbFields;
    }
}
