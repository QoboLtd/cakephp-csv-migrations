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
use Cake\Core\Configure;

$api_key = Configure::read("CsvMigrations.GoogleMaps.ApiKey");

if (empty($api_key)) {
    $attributes += [
        'type' => $type,
        'label' => $label,
        'required' => (bool)$required,
        'value' => $value,
        'placeholder' => $placeholder,
        'help' => $help,
    ];

    echo $this->Form->control($name, $attributes);

    return;
}

$this->Html->script([
        'https://maps.googleapis.com/maps/api/js?key=' . $api_key
    ],
    ['async', 'defer','block' => 'scriptBottom']
);

$this->Html->script(
    [
        'CsvMigrations.coordinatespicker',
        'CsvMigrations.coordinatespicker.editinit',
    ],
    [
        'block' => 'scriptBottom'
    ]
);

$attributes = isset($attributes) ? $attributes : [];

$randId = rand(1, 1000000);
$attributes += [
    'type' => $type,
    'label' => $label,
    'data-provide' => 'coordinates',
    'required' => (bool)$required,
    'value' => $value,
    'placeholder' => $placeholder,
    'templates' => [
        'input' => '<div class="input-group" >
                        <input type="{{type}}" name="{{name}}"{{attrs}}/>
                        <div class="input-group-btn">
                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-default-'. $randId .'"><i class="fa fa-pencil"></i></button>
                        </div>
                    </div>',
    ]
];

echo $this->Form->control($name, $attributes);
?>

<div class="modal fade coordinates_modal" id="modal-default-<?= $randId ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="coordinates-modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?= __($label); ?><?= $this->Html->help($help);?></h4>
            <div><i class="fa fa-map-marker"></i> <span class="modal_gps_value"><?= $value; ?></span></div>
        </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="coordinates-modal"><?= __('Cancel') ?></button>
        <button type="button" class="btn btn-primary save_gps" data-dismiss="coordinates-modal"><?= __('Apply') ?></button>
      </div>
    </div>
  </div>
</div>

