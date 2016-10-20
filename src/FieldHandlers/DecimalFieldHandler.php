<?php
namespace CsvMigrations\FieldHandlers;

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
        $result = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if (!empty($result) && is_numeric($result)) {
            $result = number_format($result, 2);
        }

        return $result;
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
        foreach($dbFields as &$dbField) {
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
