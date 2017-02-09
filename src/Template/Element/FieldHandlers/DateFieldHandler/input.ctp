<?php
echo $this->Form->input($name, [
    'type' => 'text',
    'label' => $label,
    'data-provide' => 'datepicker',
    'autocomplete' => 'off',
    'data-date-format' => 'yyyy-mm-dd',
    'data-date-autoclose' => true,
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
]);
