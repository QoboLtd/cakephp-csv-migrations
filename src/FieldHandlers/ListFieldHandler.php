<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;
use CsvMigrations\ListTrait;

class ListFieldHandler extends BaseFieldHandler
{
    use ListTrait;

    /**
     * Field type match pattern
     */
    const FIELD_TYPE_PATTERN = '/list\((.*?)\)/';

    /**
     * Input field html markup
     */
    const INPUT_HTML = '<div class="form-group">%s</div>';

    const VALUE_NOT_FOUND_HTML = '%s <span class="text-danger glyphicon glyphicon-exclamation-sign" title="Invalid list item" aria-hidden="true"></span>';

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
        $options = array_merge($this->defaultOptions, $options);
        $data = $this->_getFieldValueFromData($data);
        $fieldOptions = $this->_getSelectOptions($options['fieldDefinitions']->getLimit());

        $input = $this->_fieldToLabel($options);

        $input .= $this->cakeView->Form->select($this->_getFieldName($options), $fieldOptions, [
            'class' => 'form-control',
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
            'value' => $data
        ]);

        return sprintf(static::INPUT_HTML, $input);
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

        if (empty($data)) {
            return $result;
        }

        $fieldOptions = $this->_getSelectOptions($options['fieldDefinitions']->getLimit());

        if (isset($fieldOptions[$data])) {
            // Concatenate all parents together with value
            $parents = explode('.', $data);
            if (!empty($parents)) {
                $path = '';
                foreach ($parents as $parent) {
                    $path = empty($path) ? $parent : $path . '.' . $parent;
                    if (isset($fieldOptions[$path])) {
                        $result .= $fieldOptions[$path];
                    }
                }
            }
        } else {
            if (isset($options['renderAs']) && $options['renderAs'] === static::RENDER_PLAIN_VALUE) {
                $result = $data;
            } else {
                $result = sprintf(static::VALUE_NOT_FOUND_HTML, $data);
            }
        }

        return $result;
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
        $content = $this->cakeView->Form->select(
            '{{name}}',
            $this->_getSelectOptions($options['fieldDefinitions']->getLimit()),
            [
                'label' => false
            ]
        );

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
