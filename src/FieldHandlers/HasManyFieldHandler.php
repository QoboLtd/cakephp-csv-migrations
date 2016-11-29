<?php
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\View\Helper\IdGeneratorTrait;
use CsvMigrations\FieldHandlers\RelatedFieldHandler;

class HasManyFieldHandler extends RelatedFieldHandler
{
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
        // get plugin and controller names
        list($relatedPlugin, $relatedController) = pluginSplit($options['fieldDefinitions']->getLimit());
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
            $input .= $this->cakeView->Form->label($field);
        }

        if (!empty($options['embModal'])) {
            $input .= '<div class="input-group">';
        }

        $input .= $this->cakeView->Form->input($field, [
            'label' => false,
            'name' => $field . '_label',
            'id' => $field . '_label',
            'type' => 'text',
            'data-type' => 'typeahead',
            'readonly' => (bool)$data,
            'value' => null,
            'data-id' => $this->_domId($fieldName),
            'autocomplete' => 'off',
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
            'data-url' => $this->cakeView->Url->build([
                'prefix' => 'api',
                'plugin' => $relatedPlugin,
                'controller' => $relatedController,
                'action' => 'lookup.json'
            ])
        ]);

        if (!empty($options['embModal'])) {
            $input .= '<div class="input-group-btn">';
            $input .= $this->cakeView->Form->button(
                __('<span class="fa fa-link" aria-hidden="true"></span>'),
                ['class' => 'btn btn-primary', 'title' => __('Link record')]
            );

            /*
                @NOTE:
                we might have custom data-target for the modal window,
                thus we make ID out of field/emDataTarget
            */
            $dataTarget = sprintf("#%s_modal", (empty($options['emDataTarget']) ? $field : $options['emDataTarget']));

            $input .= '<button type="button" class="btn btn-default" data-toggle="modal" data-target="' . $dataTarget . '">';
            $input .= '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>';
            $input .= '</button>';
            $input .= '</div>';
            $input .= '</div>';
        }

        $input .= $this->cakeView->Form->input(
            $options['associated_table_name'] . '._ids[]',
            ['type' => 'hidden', 'value' => $data, 'id' => $this->_domId($fieldName)]
        );

        return $input;
    }

    /**
     * {@inheritDoc}
     */
    public function renderSearchInput($table, $field, array $options = [])
    {
        return false;
    }
}
