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

?>
<?php foreach ($panelFields as $subFields) : ?>
    <?php $fieldCount = 12 < count($subFields) ? 12 : count($subFields); ?>
<div class="row">
    <?php foreach ($subFields as $field) : ?>
        <?= $this->element('CsvMigrations.Field/input', [
            'factory' => $factory,
            'field' => $field,
            'fieldCount' => $fieldCount,
            'options' => $options
        ]) ?>
    <?php endforeach ?>
</div>
<?php endforeach ?>