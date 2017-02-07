<?php
namespace CsvMigrations\FieldHandlers;

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\View\Helper\IdGeneratorTrait;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

abstract class BaseRelatedFieldHandler extends BaseFieldHandler
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
     * Search operators
     *
     * @var array
     */
    public $searchOperators = [
        'is' => [
            'label' => 'is',
            'operator' => 'IN',
        ],
        'is_not' => [
            'label' => 'is not',
            'operator' => 'NOT IN',
        ],
    ];

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

        $fieldName = $this->table->aliasField($this->field);

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
            $this->cakeView->Form->label($this->field, $options['label'], ['class' => 'control-label']),
            $input
        );

        return $input;
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
        $result = null;
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
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

        $result = $this->sanitizeValue($result, $options);

        return $result;
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

        $result[$this->field]['input'] = [
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

        return $result;
    }

    /**
     * Convert CsvField to one or more DbField instances
     *
     * Simple fields from migrations CSV map one-to-one to
     * the database fields.  More complex fields can combine
     * multiple database fields for a single CSV entry.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array                                           DbField instances
     */
    public static function fieldToDb(CsvField $csvField)
    {
        $csvField->setType(static::DB_FIELD_TYPE);
        $csvField->setLimit(null);

        $dbField = DbField::fromCsvField($csvField);
        $result = [
            $csvField->getName() => $dbField,
        ];

        return $result;
    }

    /**
     * Generate input help string
     *
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
     * Get input field associated icon
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
