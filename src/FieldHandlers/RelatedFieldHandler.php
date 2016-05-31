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
    use RelatedFieldTrait;

    /**
     * Field type
     */
    const FIELD_TYPE = 'uuid';

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
}
