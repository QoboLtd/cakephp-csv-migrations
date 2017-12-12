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
namespace CsvMigrations\FieldHandlers;

class DateFieldHandler extends BaseTimeFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'date';

    /**
     * Field type
     */
    const INPUT_FIELD_TYPE = 'datepicker';

    /**
     * Date/time format
     */
    const FORMAT = 'yyyy-MM-dd';

    /**
     * Javascript date format
     */
    const JS_DATE_FORMAT = 'yyyy-mm-dd';

    /**
     * @var string $defaultConfigClass Config class to use as default
     */
    protected $defaultConfigClass = '\\CsvMigrations\\FieldHandlers\\Provider\\Config\\DateConfig';

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

        if (isset($options['element'])) {
            $content = $this->cakeView->element($options['element'], [
                'options' => [
                    'fieldName' => '{{name}}',
                    'value' => '{{value}}',
                    'type' => static::INPUT_FIELD_TYPE,
                    'label' => false
                ]
            ]);
        } else {
            $content = $this->cakeView->Form->input('{{name}}', [
                'value' => '{{value}}',
                'type' => 'text',
                'data-provide' => 'datepicker',
                'autocomplete' => 'off',
                'data-date-format' => static::JS_DATE_FORMAT,
                'data-date-autoclose' => true,
                'data-date-week-start' => 1,
                'label' => false,
                'templates' => [
                    'input' => vsprintf($this->_templates['input'], [
                        '',
                        'calendar'
                    ])
                ]
            ]);
        }

        $result[$this->field]['input'] = [
            'content' => $content,
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

        return $result;
    }
}
