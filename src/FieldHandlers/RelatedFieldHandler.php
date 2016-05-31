<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\View\Helper\IdGeneratorTrait;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

class RelatedFieldHandler extends BaseFieldHandler
{
    use IdGeneratorTrait;

    /**
     * Field type
     */
    const FIELD_TYPE = 'uuid';

    /**
     * Field type match pattern
     */
    const FIELD_TYPE_PATTERN = '/related\((.*?)\)/';

    /**
     * Action name for html link
     */
    const LINK_ACTION = 'view';

    /**
     * Method responsible for rendering field's input.
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field input
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        // load AppView
        $cakeView = new AppView();
        // get related table name
        $relatedName = $this->_getRelatedName($options['fieldDefinitions']['type']);

        $relatedProperties = $this->_getRelatedProperties($relatedName, $data);

        $fieldName = $this->_getFieldName($table, $field, $options);

        $input = '';

        $input .= $cakeView->Form->label($field);

        $input .= '<div class="input-group">';
        $input .= '<span class="input-group-addon" title="Auto-complete"><strong>&hellip;</strong></span>';

        $input .= $cakeView->Form->input($field, [
            'label' => false,
            'name' => $field . '_label',
            'id' => $field . '_label',
            'type' => 'text',
            'data-type' => 'typeahead',
            'readonly' => (bool)$data,
            'value' => $relatedProperties['dispFieldVal'],
            'data-id' => $this->_domId($fieldName),
            'autocomplete' => 'off',
            'required' => (bool)$options['fieldDefinitions']['required'],
            'data-url' => $cakeView->Url->build([
                'prefix' => 'api',
                'plugin' => $relatedProperties['plugin'],
                'controller' => $relatedProperties['controller'],
                'action' => 'lookup.json'
            ])
        ]);

        if (!empty($options['embModal'])) {
            $input .= '<div class="input-group-btn">';
            $input .= '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#' . $field . '_modal">';
            $input .= '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>';
            $input .= '</button>';
            $input .= '</div>';
        }
        $input .= '</div>';

        $input .= $cakeView->Form->input($fieldName, ['type' => 'hidden', 'value' => $data]);

        return $input;
    }

    /**
     * Method that renders related field's value.
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = null;

        if (empty($data)) {
            return $result;
        }

        // load AppView
        $cakeView = new AppView();
        // get related table name
        $relatedName = $this->_getRelatedName($options['fieldDefinitions']['type']);

        $relatedProperties[] = $this->_getRelatedProperties($relatedName, $data);

        if (!empty($relatedProperties[0]['config']['parent']['module'])) {
            array_unshift(
                $relatedProperties,
                $this->_getRelatedParentProperties($relatedProperties[0])
            );
        }

        $inputs = [];
        foreach ($relatedProperties as $properties) {
            // generate related record(s) html link
            $inputs[] = $cakeView->Html->link(
                h($properties['dispFieldVal']),
                $cakeView->Url->build([
                    'plugin' => $properties['plugin'],
                    'controller' => $properties['controller'],
                    'action' => static::LINK_ACTION,
                    $properties['id']
                ])
            );
        }

        if (!empty($inputs)) {
            $result .= implode(' &gt; ', $inputs);
        }

        return $result;
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
            static::FIELD_TYPE,
            null,
            $csvField->getRequired(),
            $csvField->getNonSearchable()
        );

        return $dbFields;
    }

    /**
     * Method that extracts list name from field type definition.
     *
     * @param  string $type field type
     * @return string       list name
     */
    protected function _getRelatedName($type)
    {
        $result = preg_replace(static::FIELD_TYPE_PATTERN, '$1', $type);

        return $result;
    }

    /**
     * Get related model's parent model properties.
     *
     * @param  array $table related model properties
     * @return void
     */
    protected function _getRelatedParentProperties($relatedProperties)
    {
        $parentTable = TableRegistry::get($relatedProperties['config']['parent']['module']);
        $foreignKey = $this->_getForeignKey($parentTable, $relatedProperties['controller']);

        return $this->_getRelatedProperties($parentTable, $relatedProperties['entity']->{$foreignKey});
    }

    /**
     * Get related model's properties.
     *
     * @param  mixed $table related table instance or name
     * @param  sting $data  query parameter value
     * @return void
     */
    protected function _getRelatedProperties($table, $data)
    {
        if (!is_object($table)) {
            $tableName = $table;
            $table = TableRegistry::get($tableName);
        } else {
            $tableName = $table->registryAlias();
        }

        $result['id'] = $data;
        $result['config'] = $table->getConfig();
        // get associated entity record
        $result['entity'] = $this->_getAssociatedRecord($table, $data);
        // get related table's displayField value
        $result['dispFieldVal'] = !empty($result['entity']->{$table->displayField()})
            ? $result['entity']->{$table->displayField()}
            : null
        ;
        // get plugin and controller names
        list($result['plugin'], $result['controller']) = pluginSplit($tableName);
        // remove vendor from plugin name
        if (!is_null($result['plugin'])) {
            $pos = strpos($result['plugin'], '/');
            if ($pos !== false) {
                $result['plugin'] = substr($result['plugin'], $pos + 1);
            }
        }

        return $result;
    }

    /**
     * Get parent model association's foreign key.
     *
     * @param  \Cake\ORM\Table $table          Table instance
     * @param  string          $controllerName Controller name
     * @return string
     */
    protected function _getForeignKey(Table $table, $controllerName)
    {
        $result = null;
        foreach ($table->associations() as $association) {
            if ($controllerName === $association->className()) {
                $result = $association->foreignKey();
            }
        }

        return $result;
    }

    /**
     * Retrieve and return associated record Entity, by primary key value.
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @param  string          $value Primary key value
     * @return object
     */
    protected function _getAssociatedRecord(Table $table, $value)
    {
        $query = $table->find('all', [
            'conditions' => [$table->primaryKey() => $value],
            'limit' => 1
        ]);

        return $query->first();
    }
}
