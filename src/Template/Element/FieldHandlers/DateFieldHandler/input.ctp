<?php
$attributes += [
    'type' => 'text',
    'label' => $label,
    'data-provide' => 'datepicker',
    'autocomplete' => 'off',
    'data-date-format' => 'yyyy-mm-dd',
    'data-date-autoclose' => true,
    'data-date-week-start' => 1,
    'required' => (bool)$required,
    'value' => $value,
    'templates' => [
        'input' => '<div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
            </div>
            <input type="{{type}}" name="{{name}}"{{attrs}}/>
        </div>'
    ]
];

echo $this->Form->input($name, $attributes);
