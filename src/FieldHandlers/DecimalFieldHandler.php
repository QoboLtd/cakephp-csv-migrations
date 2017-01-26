<?php
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\Table;
use CsvMigrations\FieldHandlers\BaseSimpleFieldHandler;

class DecimalFieldHandler extends BaseSimpleFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'decimal';

    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'number';

    /**
     * Precision
     *
     * Temporary setting for decimal precision, until
     * we learn to read it from the fields.ini.
     *
     * @todo Replace with configuration from fields.ini
     */
    const PRECISION = 2;

    /**
     * Max value
     *
     * Temporary setting for maximum value, until
     * we learn to read it from the fields.ini.
     *
     * @todo Replace with configuration from fields.ini
     */
    const MAX_VALUE = '99999999.99';

    /**
     * Render field value
     *
     * This method prepares the output of the value for the given
     * field.  The result can be controlled via the variety of
     * options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field value
     */
    public function renderValue($data, array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $data = $this->_getFieldValueFromData($data);
        $result = (float)filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if (!empty($result) && is_numeric($result)) {
            $result = number_format($result, static::PRECISION);
        } else {
            $result = (string)$result;
        }

        return $result;
    }

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

        $fieldName = $this->table->alias() . '.' . $this->field;

        $input = $this->cakeView->Form->input($fieldName, [
            'type' => static::INPUT_FIELD_TYPE,
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'step' => 'any',
            'max' => (float)static::MAX_VALUE,
            'label' => $options['label'],
        ]);

        return $input;
    }

    /**
     * Render field search input
     *
     * This method prepares the search form input for the given field,
     * including the input itself, label, pre-populated value,
     * and so on.  The result can be controlled via the variety
     * of options.
     *
     * @param  array  $options Field options
     * @return array           Array of field input HTML, pre and post CSS, JS, etc
     */
    public function renderSearchInput(array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $content = $this->cakeView->Form->input('', [
            'name' => '{{name}}',
            'value' => '{{value}}',
            'type' => static::INPUT_FIELD_TYPE,
            'step' => 'any',
            'max' => (float)static::MAX_VALUE,
            'label' => false
        ]);

        return [
            'content' => $content
        ];
    }

    /**
     * Get search operators
     *
     * This method prepares a list of search operators that
     * are appropriate for a given field.
     *
     * @return array List of search operators
     */
    public function getSearchOperators()
    {
        return [
            'is' => [
                'label' => 'is',
                'operator' => 'IN',
            ],
            'is_not' => [
                'label' => 'is not',
                'operator' => 'NOT IN',
            ],
            'greater' => [
                'label' => 'greater',
                'operator' => '>',
            ],
            'less' => [
                'label' => 'less',
                'operator' => '<',
            ],
        ];
    }

    /**
     * Convert CsvField to one or more DbField instances
     *
     * Simple fields from migrations CSV map one-to-one to
     * the database fields.  More complex fields can combine
     * multiple database fields for a single CSV entry.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array                                           DbField instances
     */
    public function fieldToDb(CsvField $csvField)
    {
        $dbFields = parent::fieldToDb($csvField);

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
}
