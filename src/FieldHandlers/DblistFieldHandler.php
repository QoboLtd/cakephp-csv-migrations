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
        // create new list if it does not exist
        $this->_createList($table, $list);

        $params = [
            'field' => $this->field,
            'name' => $fieldName,
            'type' => static::INPUT_FIELD_TYPE,
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'options' => $table->find('options', ['name' => $list])
        ];

        return $this->_renderElement(__FUNCTION__, $params, $options);
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
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
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

        $content = $this->cakeView->cell(
            'CsvMigrations.Dblist::renderInput',
            [
                '{{name}}',
                $options['fieldDefinitions']->getListName(),
                ['label' => false] + $this->_defaultInputOptions
            ]
        )->render('renderInput');

        $result[$this->field]['input'] = [
            'content' => $content
        ];

        return $result;
    }

    /**
     * Create new list.
     *
     * It will fail to create a new list if the given name already exists.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $name List's name
     * @return bool         True on sucess.
     */
    protected function _createList(Table $table, $name = '')
    {
        if ($table->exists(['name' => $name])) {
            return false;
        }

        $entity = $table->newEntity(['name' => $name]);

        return $table->save($entity);
    }
}
