<?php
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\Table;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

class DecimalFieldHandler extends BaseFieldHandler
{
    /**
     * {@inheritDoc}
     */
    const DB_FIELD_TYPE = 'decimal';

    /**
     * {@inheritDoc}
     */
    public function renderValue($data, array $options = [])
    {
        $data = $this->_getFieldValueFromData($data);
        $result = (float)filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        $args = $this->_getDbColumnArgs();

        $precision = !empty($args['precision']) ? $args['precision']: 2;

        if (!empty($result) && is_numeric($result)) {
            $result = number_format($result, $precision);
        } else {
            $result = (string)$result;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function renderInput($data = '', array $options = [])
    {
        $data = $this->_getFieldValueFromData($data);
        $fieldType = $options['fieldDefinitions']->getType();

        if (in_array($fieldType, array_keys($this->_fieldTypes))) {
            $fieldType = $this->_fieldTypes[$fieldType];
        }

        $input = $this->_fieldToLabel($options);

        $input .= $this->cakeView->Form->input($this->_getFieldName($options), [
            'type' => $fieldType,
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'step' => 'any',
            'max' => $this->_getNumberMax(),
            'label' => false
        ]);

        return $input;
    }

    /**
     * {@inheritDoc}
     */
    public function renderSearchInput(array $options = [])
    {
        $fieldType = $options['fieldDefinitions']->getType();

        if (in_array($fieldType, array_keys($this->_fieldTypes))) {
            $fieldType = $this->_fieldTypes[$fieldType];
        }

        $content = $this->cakeView->Form->input('', [
            'name' => '{{name}}',
            'value' => '{{value}}',
            'type' => $fieldType,
            'step' => 'any',
            'max' => $this->_getNumberMax(),
            'label' => false
        ]);

        return [
            'content' => $content
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function fieldToDb(CsvField $csvField, $table, $field)
    {
        $dbFields[] = new DbField(
            $csvField->getName(),
            static::DB_FIELD_TYPE,
            $csvField->getLimit(),
            $csvField->getRequired(),
            $csvField->getNonSearchable(),
            $csvField->getUnique()
        );

        // set precision and scale provided by csv migration decimal field type definition
        foreach ($dbFields as &$dbField) {
            // skip if scale and precision are not defined
            if (empty($dbField->getLimit())) {
                continue;
            }
            // skip if scale and precision are not defined correctly
            if (false === strpos($dbField->getLimit(), '.')) {
                continue;
            }

            list($precision, $scale) = explode('.', $dbField->getLimit());
            $options = $dbField->getOptions();
            $options['precision'] = $precision;
            $options['scale'] = $scale;
            $dbField->setOptions($options);
        }

        return $dbFields;
    }

    /**
     * Method that calculates max value for number input field.
     *
     * @return float
     */
    protected function _getNumberMax()
    {
        $result = null;

        $args = $this->_getDbColumnArgs();

        if (!empty($args['length'])) {
            $result = str_repeat('9', (int)$args['length']);
        }

        if ($result && !empty($args['precision'])) {
            $result = substr_replace($result, '.', $args['length'] - $args['precision'], 0);
        }

        return (float)$result;
    }
}
