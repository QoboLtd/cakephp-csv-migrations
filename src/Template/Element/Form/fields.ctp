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

use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$factory = new FieldHandlerFactory($this);

foreach ($options['fields'] as $panelName => $panelFields) : ?>
<div class="box box-primary" data-provide="dynamic-panel">
    <div class="box-header with-border">
        <h3 class="box-title" data-title="dynamic-panel-title"><?= $panelName ?></h3>
    </div>
    <div class="box-body">
        <?= $this->element('CsvMigrations.Form/fields_panel', [
            'panelFields' => $panelFields,
            'options' => $options,
            'factory' => $factory
        ]) ?>
    </div>
</div>
<?php endforeach; ?>
