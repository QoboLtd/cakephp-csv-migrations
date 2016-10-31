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
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = (float)filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        $args = $this->_getDbColumnArgs($table, $field);

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
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $fieldType = $options['fieldDefinitions']->getType();

        if (in_array($fieldType, array_keys($this->_fieldTypes))) {
            $fieldType = $this->_fieldTypes[$fieldType];
        }

        $input = $this->_fieldToLabel($field, $options);

        $input .= $this->cakeView->Form->input($this->_getFieldName($table, $field, $options), [
            'type' => $fieldType,
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'step' => 'any',
            'max' => $this->_getNumberMax($table, $field),
            'label' => false
        ]);

        return $input;
    }

    /**
     * {@inheritDoc}
     */
    public function fieldToDb(CsvField $csvField)
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
     * @param  \Cake\ORM\Table $table Table instance
     * @param  string          $field Field name
     * @return float
     */
    protected function _getNumberMax(Table $table, $field)
    {
        $result = null;

        $args = $this->_getDbColumnArgs($table, $field);

        if (!empty($args['length'])) {
            $result = str_repeat('9', (int)$args['length']);
        }

        if ($result && !empty($args['precision'])) {
            $result = substr_replace($result, '.', $args['length'] - $args['precision'], 0);
        }

        return (float)$result;
    }
}
