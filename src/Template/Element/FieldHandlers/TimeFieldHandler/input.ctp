<?php
echo $this->Form->input($name, [
    'type' => 'text',
    'label' => $label,
    'data-provide' => 'timepicker',
    'autocomplete' => 'off',
    'required' => (bool)$required,
    'value' => $value,
    'templates' => [
        'input' => '<div class="input-group bootstrap-timepicker timepicker">
            <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
            </div>
            <input type="{{type}}" name="{{name}}"{{attrs}}/>
        </div>'
    ]
]);
