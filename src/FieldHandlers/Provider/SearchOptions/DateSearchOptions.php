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

/**
 * DateSearchOptions
 *
 * Date search options
 */
class DateSearchOptions extends AbstractSearchOptions
{
    /**
     * Custom form input templates.
     *
     * @var array Associative array of templates
     */
    protected $templates = [
        'input' => '<div class="input-group %s">
            <div class="input-group-addon">
                <i class="fa fa-%s"></i>
            </div>
            <input type="{{type}}" name="{{name}}"{{attrs}}/>
        </div>'
    ];

    /**
     * Provide search options
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $defaultOptions = $this->getDefaultOptions($data, $options);

        $view = $this->config->getView();
        if (isset($options['element'])) {
            $template = $view->element($options['element'], [
                'options' => [
                    'fieldName' => '{{name}}',
                    'value' => '{{value}}',
                    'type' => 'datepicker',
                    'label' => false
                ]
            ]);
        } else {
            $template = $view->Form->input('{{name}}', [
                'value' => '{{value}}',
                'type' => 'text',
                'data-provide' => 'datepicker',
                'autocomplete' => 'off',
                'data-date-format' => 'yyyy-mm-dd',
                'data-date-autoclose' => true,
                'data-date-week-start' => 1,
                'label' => false,
                'templates' => [
                    'input' => vsprintf($this->templates['input'], [
                        '',
                        'calendar'
                    ])
                ]
            ]);
        }

        $defaultOptions['input'] = [
            'content' => $template,
            'post' => [
                [
                    'type' => 'script',
                    'content' => 'AdminLTE./plugins/datepicker/bootstrap-datepicker',
                    'block' => 'scriptBottom'
                ],
                [
                    'type' => 'css',
                    'content' => 'AdminLTE./plugins/datepicker/datepicker3',
                    'block' => 'css'
                ]
            ]
        ];

        $result[$this->config->getField()] = $defaultOptions;

        return $result;
    }
}
