<?php
namespace CsvMigrations\FieldHandlers;

use Cake\Core\Configure;
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
     * Html input markup
     */
    const HTML_INPUT = '
        <div class="input-group select2-bootstrap-prepend select2-bootstrap-append">
            <span class="input-group-addon" title="%s"><span class="fa fa-%s"></span></span>%s
        </div>';

    /**
     * Html embedded button markup
     */
    const HTML_EMBEDDED_BTN = '
        <div class="input-group-btn">
            <button class="btn btn-primary" title="Link record" type="submit">
                <span class="fa fa-link" aria-hidden="true"></span>
            </button>
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#%s_modal">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
            </button>
        </div>';

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
        $data = $this->_getFieldValueFromData($field, $data);
        $relatedProperties = $this->_getRelatedProperties($options['fieldDefinitions']->getLimit(), $data);

        if (!empty($relatedProperties['dispFieldVal']) && !empty($relatedProperties['config']['parent']['module'])) {
            $relatedParentProperties = $this->_getRelatedParentProperties($relatedProperties);
            if (!empty($relatedParentProperties['dispFieldVal'])) {
                $relatedProperties['dispFieldVal'] = implode(' ' . $this->_separator . ' ', [
                    $relatedParentProperties['dispFieldVal'],
                    $relatedProperties['dispFieldVal']
                ]);
            }
        }

        $fieldName = $this->_getFieldName($table, $field, $options);

        // create select input
        $input = $this->cakeView->Form->input($options['associated_table_name'] . '._ids', [
            'options' => [$data => $relatedProperties['dispFieldVal']],
            'label' => false,
            'id' => $field,
            'type' => 'select',
            'title' => $this->_getInputHelp($relatedProperties),
            'data-type' => 'select2',
            'data-display-field' => $relatedProperties['displayField'],
            'escape' => false,
            'data-id' => $this->_domId($fieldName),
            'autocomplete' => 'off',
            'required' => (bool)$options['fieldDefinitions']->getRequired(),
            'data-url' => $this->cakeView->Url->build([
                'prefix' => 'api',
                'plugin' => $relatedProperties['plugin'],
                'controller' => $relatedProperties['controller'],
                'action' => 'lookup.json'
            ]),
            'multiple' => 'multiple',
            'value' => $relatedProperties['dispFieldVal']
        ]);

        // append embedded modal button
        $input .= sprintf(
            static::HTML_EMBEDDED_BTN,
            empty($options['emDataTarget']) ? $field : $options['emDataTarget']
        );

        // create input html
        $input = sprintf(
            static::HTML_INPUT,
            $relatedProperties['controller'],
            $this->_getInputIcon($relatedProperties),
            $input
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
