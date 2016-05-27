<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use CsvMigrations\FieldHandlers\ListFieldHandler;

class MoneyFieldHandler extends ListFieldHandler
{
    /**
     * Money fields
     *
     * @var array
     */
    protected $_fields = [
        'currency' => [
            'type' => 'string',
            'field' => 'select',
            'list' => 'currencies'
        ],
        'amount' => [
            'type' => 'string',
            'field' => 'input'
        ]
    ];

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
        // load AppView
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
                    $selectOptions = $this->_getSelectOptions($preOptions['list']);
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
                    $selectOptions = $this->_getSelectOptions($preOptions['list']);
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
     * Method responsible for converting csv field instance to database field instance.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array list of DbField instances
     */
    public function fieldToDb(CsvField $csvField)
    {
        foreach ($this->_fields as $suffix => $options) {
            $dbFields[] = new DbField(
                $csvField->getName() . '_' . $suffix,
                $options['type'],
                $csvField->getLimit(),
                $csvField->getRequired(),
                $csvField->getNonSearchable()
            );
        }

        return $dbFields;
    }
}
