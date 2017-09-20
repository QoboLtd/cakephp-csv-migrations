<?php
$attributes += [
    'type' => 'text',
    'label' => $label,
    'data-provide' => 'datetimepicker',
    'autocomplete' => 'off',
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
