<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\TableRegistry;

class DblistFieldHandler extends BaseListFieldHandler
{
    /**
     * Field type
     */
    const INPUT_FIELD_TYPE = 'select';

    /**
     * @var string $defaultConfigClass Config class to use as default
     */
    protected $defaultConfigClass = '\\CsvMigrations\\FieldHandlers\\Provider\\Config\\DblistConfig';

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
        $result = $this->_getFieldValueFromData($data, $this->field);

        if (empty($options['listName'])) {
            $options['listName'] = $options['fieldDefinitions']->getListName();
        }

        return parent::renderValue($data, $options);
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
        $data = $this->_getFieldValueFromData($data, $this->field);
        if (empty($data) && !empty($options['default'])) {
            $data = $options['default'];
        }

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
