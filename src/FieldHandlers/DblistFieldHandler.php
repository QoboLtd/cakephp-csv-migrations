<?php
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use CsvMigrations\FieldHandlers\BaseListFieldHandler;

class DblistFieldHandler extends BaseListFieldHandler
{
    /**
     * Field type
     */
    const INPUT_FIELD_TYPE = 'select';

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

        $list = $options['fieldDefinitions']->getListName();
        $table = TableRegistry::get('CsvMigrations.Dblists');

        $params = [
            'field' => $this->field,
            'name' => $fieldName,
            'type' => static::INPUT_FIELD_TYPE,
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'options' => $table->find('options', ['name' => $list]),
        ];

        return $this->_renderElement(__FUNCTION__, $params, $options);
    }

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
    public function formatValue($data, array $options = [])
    {
        //CsvField object is mandatory
        if (!isset($options['fieldDefinitions']) ||
            !($options['fieldDefinitions'] instanceof CsvField)) {
            return $data;
        }

        $list = $options['fieldDefinitions']->getListName();

        return $this->cakeView->cell('CsvMigrations.Dblist::renderValue', [$data, $list])->render('renderValue');
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

        $list = $options['fieldDefinitions']->getListName();

        $table = TableRegistry::get('CsvMigrations.Dblists');

        $params = [
            'field' => $this->field,
            'name' => '{{name}}',
            'type' => static::INPUT_FIELD_TYPE,
            'label' => false,
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => '{{value}}',
            'options' => $table->find('options', ['name' => $list])
        ];

        $result[$this->field]['input'] = [
            'content' => $this->_renderElement('renderInput', $params, $options)
        ];

        return $result;
    }
}
