<?php
$attributes += [
    'options' => [$value => $relatedProperties['dispFieldVal']],
    'label' => false,
    'id' => $field,
    'type' => $type,
    'title' => $title,
    'data-type' => 'select2',
    'data-display-field' => $relatedProperties['displayField'],
    'escape' => false,
    'autocomplete' => 'off',
    'required' => (bool)$required,
    'multiple' => 'multiple',
    'data-url' => $this->Url->build([
        'prefix' => 'api',
        'plugin' => $relatedProperties['plugin'],
        'controller' => $relatedProperties['controller'],
        'action' => 'lookup.json'
    ]),
];
?>
<div class="input-group select2-bootstrap-prepend select2-bootstrap-append">
    <span class="input-group-addon" title="<?= $relatedProperties['controller'] ?>">
        <span class="fa fa-<?= $icon ?>"></span>
    </span>
        <?= $this->Form->input($name . '._ids', $attributes); ?>

    <div class="input-group-btn">
        <button class="btn btn-primary" title="Link record" type="submit">
            <i class="fa fa-link" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#<?= $embedded ?>_modal">
            <i class="fa fa-plus" aria-hidden="true"></i>
        </button>
    </div>
</div>