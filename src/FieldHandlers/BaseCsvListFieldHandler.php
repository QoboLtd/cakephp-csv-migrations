<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseListFieldHandler;
use CsvMigrations\ListTrait;

/**
 * BaseCsvListFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to list field handlers, which rely on CSV
 * files and use dot notation to store nested values.
 */
abstract class BaseCsvListFieldHandler extends BaseListFieldHandler
{
    use ListTrait;

    /**
     * HTML to add to invalid list items
     */
    const VALUE_NOT_FOUND_HTML = '%s <span class="text-danger glyphicon glyphicon-exclamation-sign" title="Invalid list item" aria-hidden="true"></span>';

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
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
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

        $content = $this->cakeView->Form->select(
            '{{name}}',
            $this->_getSelectOptions($options['fieldDefinitions']->getLimit()),
            [
                'label' => false
            ]
        );

        $result[$this->field]['input'] = [
            'content' => $content
        ];

        return $result;
    }
}
