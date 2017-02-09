<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

/**
 * BaseCombinedFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to combined field handlers.
 */
abstract class BaseCombinedFieldHandler extends BaseFieldHandler
{
    /**
     * Combined fields
     *
     * @var array
     */
    protected static $_fields = [];

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

        $label = $options['label'];

        $inputs = [];
        foreach (static::$_fields as $suffix => $preOptions) {
            // $options['fieldDefinitions']->setType($preOptions['handler']::DB_FIELD_TYPE);
            // Skip individual inputs' label
            $options['label'] = false;
            $fieldName = $this->field . '_' . $suffix;

            $fieldData = $this->_getFieldValueFromData($data, $fieldName);
            if (empty($fieldData) && !empty($options['entity'])) {
                $fieldData = $this->_getFieldValueFromData($options['entity'], $fieldName);
            }

            $handler = new $preOptions['handler']($this->table, $fieldName, $this->cakeView);

            $inputs[] = $handler->renderInput($fieldData, $options);
        }

        $params = [
            'field' => $this->field,
            'label' => $label,
            'required' => $options['fieldDefinitions']->getRequired(),
            'inputs' => $inputs
        ];

        return $this->_renderElement(__FUNCTION__, $params, $options);
    }

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
        $result = [];
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        foreach (static::$_fields as $suffix => $fieldOptions) {
            $fieldName = $this->field . '_' . $suffix;
            $fieldData = $this->_getFieldValueFromData($data, $fieldName);
            // fieldData will most probably be empty when dealing with combined fields for
            // example, field 'salary' will have no data since is converted to 'salary_amount'
            // and 'salary_currency'. In these cases we just re-call _getFeildValueFromData
            // method and we pass to it the whole entity.
            if (empty($fieldData) && !empty($options['entity'])) {
                $fieldData = $this->_getFieldValueFromData($options['entity'], $fieldName);
            }
            $handler = new $fieldOptions['handler']($this->table, $fieldName, $this->cakeView);
            $result[] = $handler->renderValue($fieldData, $options);
        }

        $result = implode('&nbsp;', $result);
        $result = $this->sanitizeValue($result, $options);

        return $result;
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
        $result = [];
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        foreach (static::$_fields as $suffix => $fieldOptions) {
            $options['fieldDefinitions']->setType($fieldOptions['handler']::DB_FIELD_TYPE);
            $fieldName = $this->field . '_' . $suffix;
            $handler = new $fieldOptions['handler']($this->table, $fieldName, $this->cakeView);
            $fieldOptions = $handler->getSearchOptions($options);
            if (!empty($fieldOptions)) {
                $result = array_merge($result, $fieldOptions);
            }
        }

        return $result;
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
    public static function fieldToDb(CsvField $csvField)
    {
        $dbFields = [];
        foreach (static::$_fields as $suffix => $options) {
            $subField = clone $csvField;
            $subField->setName($csvField->getName() . '_' . $suffix);
            if (isset($options['limit'])) {
                $subField->setLimit($options['limit']);
            }

            $dbFields = array_merge($dbFields, $options['handler']::fieldToDb($subField));
        }

        return $dbFields;
    }
}
