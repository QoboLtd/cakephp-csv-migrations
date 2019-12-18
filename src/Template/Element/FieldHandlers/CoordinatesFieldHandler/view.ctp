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

$api_key = Configure::read("CsvMigrations.coordinates_field.GoogleMapKey");
?>

<?php if (isset($options['renderAs']) && $options['renderAs'] === 'plain' || empty($api_key)): ?>

   <?php echo $result; ?>

<?php else:
$this->Html->script([
        'https://maps.googleapis.com/maps/api/js?key=' . $api_key
    ],
    [
        'async',
        'defer',
        'block' => 'scriptBottom'
    ]
);

$this->Html->script(
    [
        'CsvMigrations.coordinatespicker',
        'CsvMigrations.coordinatespicker.viewinit',
    ],
    [
        'block' => 'scriptBottom'
    ]
);

$randId = rand(1, 1000000);

?>

<div class="view-map-container" data-gps="<?php echo $result; ?>">
    <div class="gps-string"></div>
    <div style="height:300px" class="view-googlemap" id="view-map-<?php echo $randId; ?>"></div>
</div>

<?php endif; ?>
