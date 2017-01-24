<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;

class DblistFieldHandler extends BaseFieldHandler
{
    /**
     * Field type
     */
    const DB_FIELD_TYPE = 'string';

    /**
     * Input default options
     *
     * @var array
     */
    protected $_defaultOptions = [
        'class' => 'form-control',
        'label' => true
    ];

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
        $result = '';
        $options = array_merge($this->defaultOptions, $options);
        $data = $this->_getFieldValueFromData($data);
        //CsvField object is mandatory
        if (!isset($options['fieldDefinitions']) ||
            !($options['fieldDefinitions'] instanceof CsvField)) {
            return $result;
        }
        $csvObj = $options['fieldDefinitions'];
        $list = $csvObj->getListName();
        $fieldName = $this->_getFieldName($options);
        $options = [
            'value' => $data,
            'required' => $csvObj->getRequired(),
        ];
        $options += $this->_defaultOptions;
        $result = $this->cakeView->cell('CsvMigrations.Dblist::' . __FUNCTION__, [$fieldName, $list, $options])->render(__FUNCTION__);

        return $result;
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
        $result = '';
        $options = array_merge($this->defaultOptions, $options);
        $data = $this->_getFieldValueFromData($data);

        //CsvField object is mandatory
        if (!isset($options['fieldDefinitions']) ||
            !($options['fieldDefinitions'] instanceof CsvField)) {
            return $result;
        }
        $csvObj = $options['fieldDefinitions'];
        $list = $csvObj->getListName();

        return $this->cakeView->cell('CsvMigrations.Dblist::' . __FUNCTION__, [$data, $list])->render(__FUNCTION__);
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
        $options = array_merge($this->defaultOptions, $options);
        $content = $this->cakeView->cell(
            'CsvMigrations.Dblist::renderInput',
            [
                '{{name}}',
                $options['fieldDefinitions']->getListName(),
                ['label' => false] + $this->_defaultOptions
            ]
        )->render('renderInput');

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
        $dbFields[] = new DbField(
            $csvField->getName(),
            static::DB_FIELD_TYPE,
            null,
            $csvField->getRequired(),
            $csvField->getNonSearchable(),
            $csvField->getUnique()
        );

        return $dbFields;
    }
}
