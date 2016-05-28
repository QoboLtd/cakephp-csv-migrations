<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\View\Helper\IdGeneratorTrait;
use CsvMigrations\FieldHandlers\RelatedFieldHandler;

class HasManyFieldHandler extends RelatedFieldHandler
{
    /**
     * Field type match pattern
     */
    const FIELD_TYPE_PATTERN = '/hasMany\((.*?)\)/';

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

        if (empty($options['embModal'])) {
            $input .= $cakeView->Form->label($field);
        }

        if (!empty($options['embModal'])) {
            $input .= '<div class="input-group">';
        }

        $input .= $cakeView->Form->input($field, [
            'label' => false,
            'name' => $field . '_label',
            'id' => $field . '_label',
            'type' => 'text',
            'data-type' => 'typeahead',
            'readonly' => (bool)$data,
            'value' => null,
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
            $input .= $cakeView->Form->button(
                __('<span class="fa fa-link" aria-hidden="true"></span>'),
                ['class' => 'btn btn-primary', 'title' => __('Link record')]
            );
            $input .= '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#' . $field . '_modal">';
            $input .= '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>';
            $input .= '</button>';
            $input .= '</div>';
            $input .= '</div>';
        }

        $input .= $cakeView->Form->input(
            $options['associated_table_name'] . '._ids[]',
            ['type' => 'hidden', 'value' => $data, 'id' => $this->_domId($fieldName)]
        );

        return $input;
    }
}
