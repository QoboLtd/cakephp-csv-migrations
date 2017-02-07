<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseSimpleFieldHandler;

abstract class BaseNumberFieldHandler extends BaseSimpleFieldHandler
{
    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'number';

    /**
     * Step size to use for number field
     */
    const INPUT_FIELD_STEP = 1;

    /**
     * Precision
     *
     * Temporary setting for decimal precision, until
     * we learn to read it from the fields.ini.
     *
     * @todo Replace with configuration from fields.ini
     */
    const PRECISION = 0;

    /**
     * Max value
     *
     * Temporary setting for maximum value, until
     * we learn to read it from the fields.ini.
     *
     * @todo Replace with configuration from fields.ini
     */
    const MAX_VALUE = '99999999999';

    /**
     * Search operators
     *
     * @var array
     */
    public $searchOperators = [
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

    /**
     * Format field value
     *
     * This method provides a customization point for formatting
     * of the field value before rendering.
     *
     * NOTE: The value WILL NOT be sanitized during the formatting.
     *       It is assumed that sanitization happens either before
     *       or after this method is called.
     *
     * @param mixed $data    Field value data
     * @param array $options Field formatting options
     * @return string
     */
    protected function formatValue($data, array $options = [])
    {
        $result = (float)$data;

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

        $fieldName = $this->table->aliasField($this->field);

        $input = $this->cakeView->Form->input($fieldName, [
            'type' => static::INPUT_FIELD_TYPE,
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'step' => static::INPUT_FIELD_STEP,
            'max' => static::MAX_VALUE,
            'label' => $options['label'],
        ]);

        return $input;
    }

    /**
     * Get options for field search
     *
     * This method prepares an array of search options, which includes
     * label, form input, supported search operators, etc.  The result
     * can be controlled with a variety of options.
     *
     * @param  array  $options Field options
     * @return array           Array of field input HTML, pre and post CSS, JS, etc
     */
    public function getSearchOptions(array $options = [])
    {
        // Fix options as early as possible
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $result = parent::getSearchOptions($options);
        if (empty($result[$this->field]['input'])) {
            return $result;
        }

        $content = $this->cakeView->Form->input('', [
            'name' => '{{name}}',
            'value' => '{{value}}',
            'type' => static::INPUT_FIELD_TYPE,
            'step' => static::INPUT_FIELD_STEP,
            'max' => static::MAX_VALUE,
            'label' => false
        ]);

        $result[$this->field]['input'] = [
            'content' => $content
        ];

        return $result;
    }
}
