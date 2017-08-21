<?php
namespace CsvMigrations\FieldHandlers;

class DatetimeFieldHandler extends BaseTimeFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'datetime';

    /**
     * Input field type
     */
    const INPUT_FIELD_TYPE = 'datetimepicker';

    /**
     * Date/time format
     */
    const FORMAT = 'yyyy-MM-dd HH:mm';

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
            $content = $this->cakeView->Form->input('', [
                'name' => '{{name}}',
                'value' => '{{value}}',
                'type' => 'text',
                'data-provide' => 'datetimepicker',
                'autocomplete' => 'off',
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
                    'content' => [
                        'CsvMigrations.dom-observer',
                        'AdminLTE./plugins/daterangepicker/moment.min',
                        'AdminLTE./plugins/daterangepicker/daterangepicker',
                        'CsvMigrations.datetimepicker.init'
                    ],
                    'block' => 'scriptBottom'
                ],
                [
                    'type' => 'css',
                    'content' => 'AdminLTE./plugins/daterangepicker/daterangepicker-bs3',
                    'block' => 'css'
                ]
            ]
        ];

        return $result;
    }
}
