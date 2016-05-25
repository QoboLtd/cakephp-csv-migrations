<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
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
        // get related table's displayField value
        $displayFieldValue = $this->_getDisplayFieldValueByPrimaryKey($relatedName, $data);
        // get plugin and controller names
        list($relatedPlugin, $relatedController) = pluginSplit($relatedName);
        // remove vendor from plugin name
        if (!is_null($relatedPlugin)) {
            $pos = strpos($relatedPlugin, '/');
            if ($pos !== false) {
                $relatedPlugin = substr($relatedPlugin, $pos + 1);
            }
        }

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
            'value' => $displayFieldValue,
            'data-id' => $this->_domId($fieldName),
            'autocomplete' => 'off',
            'required' => (bool)$options['fieldDefinitions']['required'],
            'data-url' => $cakeView->Url->build([
                'prefix' => 'api',
                'plugin' => $relatedPlugin,
                'controller' => $relatedController,
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
        // load AppView
        $cakeView = new AppView();
        // get related table name
        $relatedName = $this->_getRelatedName($options['fieldDefinitions']['type']);
        // get related table's displayField value
        $displayFieldValue = $this->_getDisplayFieldValueByPrimaryKey($relatedName, $data);
        // get plugin and controller names
        list($relatedPlugin, $relatedController) = pluginSplit($relatedName);
        // remove vendor from plugin name
        if (!is_null($relatedPlugin)) {
            $pos = strpos($relatedPlugin, '/');
            if ($pos !== false) {
                $relatedPlugin = substr($relatedPlugin, $pos + 1);
            }
        }

        $result = null;

        if (empty($data)) {
            return $result;
        }

        // generate related record html link
        $result = $cakeView->Html->link(
            h($displayFieldValue),
            $cakeView->Url->build([
                'plugin' => $relatedPlugin,
                'controller' => $relatedController,
                'action' => static::LINK_ACTION,
                $data
            ])
        );

        return $result;
    }

    /**
     * Method responsible for converting csv field instance to database field instance.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return \CsvMigrations\FieldHandlers\DbField            DbField instance
     */
    public function fieldToDb(CsvField $csvField)
    {
        $dbField = new DbField(
            $csvField->getName(),
            static::FIELD_TYPE,
            null,
            $csvField->getRequired(),
            $csvField->getNonSearchable()
        );

        return $dbField;
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
     * Method that retrieves provided Table's displayField value,
     * based on provided primary key's value.
     *
     * @param  mixed  $table      Table object or name
     * @param  sting  $value      query parameter value
     * @return string             displayField value
     */
    protected function _getDisplayFieldValueByPrimaryKey($table, $value)
    {
        $result = '';

        if (!is_object($table)) {
            $table = TableRegistry::get($table);
        }
        $primaryKey = $table->primaryKey();
        $displayField = $table->displayField();

        $query = $table->find('all', [
            'conditions' => [$primaryKey => $value],
            'fields' => [$displayField],
            'limit' => 1
        ]);

        $record = $query->first();

        if (!is_null($record)) {
            $result = $record->$displayField;
        }

        return $result;
    }
}
