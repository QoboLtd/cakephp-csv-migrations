<?php
namespace CsvMigrations\FieldHandlers;

use Cake\I18n\Date;
use Cake\I18n\Time;
use CsvMigrations\FieldHandlers\BaseStringFieldHandler;

/**
 * BaseTimeFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to date and time field handlers.
 */
abstract class BaseTimeFieldHandler extends BaseStringFieldHandler
{
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
                'label' => 'from',
                'operator' => '>',
            ],
            'less' => [
                'label' => 'to',
                'operator' => '<',
            ],
    ];

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
        if (is_object($data)) {
            $result = $data->i18nFormat(static::FORMAT);
        } else {
            $result = (string)$data;
        }
        $result = $this->sanitizeValue($result, $options);
        $result = $this->formatValue($result, $options);

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

        if ($data instanceof Time) {
            $data = $data->i18nFormat(static::FORMAT);
        }
        if ($data instanceof Date) {
            $data = $data->i18nFormat(static::FORMAT);
        }

        return parent::renderInput($data, $options);
    }
}
