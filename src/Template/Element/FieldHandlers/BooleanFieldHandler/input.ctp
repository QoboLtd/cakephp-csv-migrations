<?php
$label = $this->Form->label($name, $label);

echo $this->Form->input($name, [
    'type' => $type,
    'class' => 'square' . ( (isset($extraClasses) && !empty($extraClasses)) ? ' ' . $extraClasses : null ),
    'required' => (bool)$required,
    'checked' => (bool)$value,
    'label' => false,
    'templates' => [
        'inputContainer' => '<div class="{{required}}">' . $label . '<div class="clearfix"></div>{{content}}</div>'
    ]
]);
