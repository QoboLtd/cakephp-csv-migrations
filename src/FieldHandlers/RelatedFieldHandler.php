<?php
namespace CsvMigrations\FieldHandlers;

use Cake\Core\Configure;
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
    const DB_FIELD_TYPE = 'uuid';

    /**
     * Action name for html link
     */
    const LINK_ACTION = 'view';

    /**
     * Suffix for label field
     */
    const LABEL_FIELD_SUFFIX = '_label';

    /**
     * Html input wrapper markup
     */
    const HTML_INPUT_WRAPPER = '<div class="form-group%s">%s%s</div>';

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
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#%s_modal">
                <i class="fa fa-plus" aria-hidden="true"></i>
            </button>
        </div>';

    /**
     * Method responsible for rendering field's input.
     *
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field input
     */
    public function renderInput($data = '', array $options = [])
    {
        $data = $this->_getFieldValueFromData($data);
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

        $fieldName = $this->_getFieldName($options);

        // create select input
        $input = $this->cakeView->Form->input($fieldName, [
            'options' => [$data => $relatedProperties['dispFieldVal']],
            'label' => false,
            'id' => $this->field,
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
            ])
        ]);

        // append embedded modal button
        if (!empty($options['embModal'])) {
            $input .= sprintf(static::HTML_EMBEDDED_BTN, $this->field);
        }

        // create input html
        $input = sprintf(
            static::HTML_INPUT,
            $relatedProperties['controller'],
            $this->_getInputIcon($relatedProperties),
            $input
        );

        // wrap input html
        $input = sprintf(
            static::HTML_INPUT_WRAPPER,
            (bool)$options['fieldDefinitions']->getRequired() ? ' required' : '',
            $this->cakeView->Form->label($this->field, null, ['class' => 'control-label']),
            $input
        );

        return $input;
    }

    /**
     * Method that renders related field's value.
     *
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($data, array $options = [])
    {
        $result = null;
        $data = $this->_getFieldValueFromData($data);

        if (empty($data)) {
            return $result;
        }

        $relatedProperties[] = $this->_getRelatedProperties($options['fieldDefinitions']->getLimit(), $data);

        if (!empty($relatedProperties[0]['config']['parent']['module'])) {
            array_unshift(
                $relatedProperties,
                $this->_getRelatedParentProperties($relatedProperties[0])
            );
        }

        $inputs = [];
        foreach ($relatedProperties as $properties) {
            if (empty($properties)) {
                continue;
            }

            if (isset($options['renderAs']) && $options['renderAs'] === static::RENDER_PLAIN_VALUE) {
                $inputs[] = h($properties['dispFieldVal']);
            } else {
                // generate related record(s) html link
                $inputs[] = $this->cakeView->Html->link(
                    h($properties['dispFieldVal']),
                    $this->cakeView->Url->build([
                        'prefix' => false,
                        'plugin' => $properties['plugin'],
                        'controller' => $properties['controller'],
                        'action' => static::LINK_ACTION,
                        $properties['id']
                    ]),
                    ['class' => 'label label-primary']
                );
            }
        }

        if (!empty($inputs)) {
            $result .= implode(' ' . $this->_separator . ' ', $inputs);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function renderSearchInput(array $options = [])
    {
        $relatedProperties = $this->_getRelatedProperties($options['fieldDefinitions']->getLimit(), null);

        $content = sprintf(
            static::HTML_INPUT,
            $relatedProperties['controller'],
            $this->_getInputIcon($relatedProperties),
            $this->cakeView->Form->input($this->field, [
                'label' => false,
                'options' => ['{{value}}' => ''],
                'name' => '{{name}}',
                'id' => $this->field,
                'type' => 'select',
                'title' => $this->_getInputHelp($relatedProperties),
                'data-type' => 'select2',
                'data-display-field' => $relatedProperties['displayField'],
                'escape' => false,
                'autocomplete' => 'off',
                'data-url' => $this->cakeView->Url->build([
                    'prefix' => 'api',
                    'plugin' => $relatedProperties['plugin'],
                    'controller' => $relatedProperties['controller'],
                    'action' => 'lookup.json'
                ])
            ])
        );

        return [
            'content' => $content,
            'post' => [
                [
                    'type' => 'script',
                    'content' => [
                        'CsvMigrations.dom-observer',
                        'AdminLTE./plugins/select2/select2.full.min',
                        'CsvMigrations.select2.init'
                    ],
                    'block' => 'scriptBotton'
                ],
                [
                    'type' => 'scriptBlock',
                    'content' => 'csv_migrations_select2.setup(' . json_encode(
                        array_merge(
                            Configure::read('CsvMigrations.select2'),
                            Configure::read('CsvMigrations.api')
                        )
                    ) . ');',
                    'block' => 'scriptBotton'
                ],
                [
                    'type' => 'css',
                    'content' => [
                        'AdminLTE./plugins/select2/select2.min',
                        'CsvMigrations.select2-bootstrap.min',
                        'CsvMigrations.style'
                    ],
                    'block' => 'css'
                ]
            ]
        ];
    }

    public function getSearchOperators()
    {
        return [
            'is' => [
                'label' => 'is',
                'operator' => 'IN',
            ],
            'is_not' => [
                'label' => 'is not',
                'operator' => 'NOT IN',
            ],
        ];
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
            static::DB_FIELD_TYPE,
            null,
            $csvField->getRequired(),
            $csvField->getNonSearchable(),
            $csvField->getUnique()
        );

        return $dbFields;
    }

    /**
     * Method that generates input help string.
     * Can be used as a value for placeholder or title attributes.
     *
     * @param array $properties Input properties
     * @return string
     */
    protected function _getInputHelp($properties)
    {
        $result = '';
        // use typeahead fields
        if (!empty($properties['config']['table']['typeahead_fields'])) {
            $result = explode(',', $properties['config']['table']['typeahead_fields']);
            if (!empty($result)) {
                $result = implode(', or ', array_map(function ($value) {
                    return Inflector::humanize($value);
                }, $result));
            }
        }
        // if typeahead fields were not defined, use display field
        if (empty($result)) {
            $result = Inflector::humanize($properties['displayField']);
        }

        return $result;
    }

    /**
     * Method that returns input field associated icon.
     *
     * @param array $properties Input properties
     * @return string
     */
    protected function _getInputIcon($properties)
    {
        // return default icon if none is defined
        if (empty($properties['config']['table']['icon'])) {
            return Configure::read('CsvMigrations.default_icon');
        }

        return $properties['config']['table']['icon'];
    }
}
