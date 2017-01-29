<?php
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\Table;
use CsvMigrations\FieldHandlers\BaseNumberFieldHandler;

class DecimalFieldHandler extends BaseNumberFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'decimal';

    /**
     * Step size to use for number field
     */
    const INPUT_FIELD_STEP = 'any';

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
     * Sanitize field value
     *
     * This method filters the value and removes anything
     * potentially dangerous.  Ideally, it should always be
     * called before rendering the value to the user, in
     * order to avoid cross-site scripting (XSS) attacks.
     *
     * @throws \InvalidArgumentException when data is not a string
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field value
     */
    public function sanitizeValue($data, array $options = [])
    {
        $data = parent::sanitizeValue($data, $options);
        $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        return $data;
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
