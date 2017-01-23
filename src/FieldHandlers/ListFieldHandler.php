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
     * Method responsible for rendering field's input.
     *
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field input
     */
    public function renderInput($data = '', array $options = [])
    {
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
     * Method that renders list field's value.
     *
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($data, array $options = [])
    {
        $result = '';
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
     * {@inheritDoc}
     */
    public function renderSearchInput(array $options = [])
    {
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
     * Method responsible for converting csv field instance to database field instance.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array list of DbField instances
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
