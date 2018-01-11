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
namespace CsvMigrations\FieldHandlers\Provider\SearchOptions;

use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\RelatedFieldTrait;

/**
 * RelatedSearchOptions
 *
 * Related search options
 */
class RelatedSearchOptions extends AbstractSearchOptions
{
    use RelatedFieldTrait;

    /**
     * Html input markup
     */
    const HTML_INPUT = '
        <div class="input-group select2-bootstrap-prepend select2-bootstrap-append">
            <span class="input-group-addon" title="%s"><span class="fa fa-%s"></span></span>%s
        </div>';

    /**
     * Provide search options
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $field = $this->config->getField();

        $template = $this->getBasicTemplate('text');
        $defaultOptions = $this->getDefaultOptions($data, $options);
        $defaultOptions['input'] = ['content' => $template];

        $result[$field] = $defaultOptions;

        $relatedProperties = $this->_getRelatedProperties($options['fieldDefinitions']->getLimit(), null);

        $view = $this->config->getView();

        $content = sprintf(
            static::HTML_INPUT,
            $relatedProperties['controller'],
            $this->_getInputIcon($relatedProperties),
            $view->Form->input($field, [
                'label' => false,
                'options' => ['{{value}}' => ''],
                'name' => '{{name}}',
                'id' => $field,
                'type' => 'select',
                'title' => $this->_getInputHelp($relatedProperties),
                'data-type' => 'select2',
                'data-display-field' => $relatedProperties['displayField'],
                'escape' => false,
                'autocomplete' => 'off',
                'data-url' => $view->Url->build([
                    'prefix' => 'api',
                    'plugin' => $relatedProperties['plugin'],
                    'controller' => $relatedProperties['controller'],
                    'action' => 'lookup.json'
                ])
            ])
        );

        $result[$field]['source'] = $options['fieldDefinitions']->getLimit();
        $result[$field]['input'] = [
            'content' => $content,
            'post' => [
                [
                    'type' => 'script',
                    'content' => [
                        'CsvMigrations.dom-observer',
                        'AdminLTE./plugins/select2/select2.full.min',
                        'CsvMigrations.select2.init'
                    ],
                    'block' => 'scriptBottom'
                ],
                [
                    'type' => 'scriptBlock',
                    'content' => 'csv_migrations_select2.setup(' . json_encode(
                        array_merge(
                            Configure::read('CsvMigrations.select2'),
                            Configure::read('CsvMigrations.api')
                        )
                    ) . ');',
                    'block' => 'scriptBottom'
                ],
                [
                    'type' => 'css',
                    'content' => [
                        'AdminLTE./plugins/select2/select2.min',
                        'Qobo/Utils.select2-bootstrap.min',
                        'Qobo/Utils.select2-style'
                    ],
                    'block' => 'css'
                ]
            ]
        ];

        return $result;
    }
}
