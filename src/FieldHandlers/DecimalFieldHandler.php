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
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'number';

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

        $input = $this->_fieldToLabel($options);

        $input .= $this->cakeView->Form->input($this->_getFieldName($options), [
            'type' => static::INPUT_FIELD_TYPE,
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
        $content = $this->cakeView->Form->input('', [
            'name' => '{{name}}',
            'value' => '{{value}}',
            'type' => static::INPUT_FIELD_TYPE,
            'step' => 'any',
            'max' => $this->_getNumberMax(),
            'label' => false
        ]);

        return [
            'content' => $content
        ];
    }

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
