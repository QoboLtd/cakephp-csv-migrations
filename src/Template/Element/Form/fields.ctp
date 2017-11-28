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

$embeddedDirty = false;

foreach ($options['fields'] as $panelName => $panelFields) : ?>
<div class="box box-solid" data-provide="dynamic-panel">
    <div class="box-header with-border">
        <h3 class="box-title" data-title="dynamic-panel-title"><?= $panelName ?></h3>
    </div>
    <div class="box-body">
    <?php foreach ($panelFields as $subFields) : ?>
        <div class="row">
        <?php foreach ($subFields as $field) : ?>
            <?php if ('' === trim($field['name'])) : ?>
                <div class="col-xs-12 col-md-6">&nbsp;</div>
                <?php continue; ?>
            <?php endif; ?>
            <?php
            // embedded field
            if ('EMBEDDED' === $field['name']) {
                $embeddedDirty = true;
                continue;
            }

            $handlerOptions = $options['handlerOptions'];

            if ($embeddedDirty) {
                $handlerOptions['embModal'] = true;
                $field['name'] = substr($field['name'], strrpos($field['name'], '.') + 1);
            }
            ?>
            <div class="col-xs-12 col-md-6 field-wrapper">
            <?php
            // non-embedded field
            $tableName = $field['model'];
            if (!is_null($field['plugin'])) {
                $tableName = $field['plugin'] . '.' . $tableName;
            }

            // get value from entity.
            $value = $options['entity']->get($field['name']);
            if (!$value) {
                // allowing query params to define field values.
                if ($this->request->query($field['name'])) {
                    $value = $this->request->query($field['name']);
                }
                if ($this->request->data($field['name'])) {
                    $value = $this->request->data($field['name']);
                }
            }

            $input = $factory->renderInput($tableName, $field['name'], $value, $handlerOptions);

            switch (gettype($input)) {
                case 'string':
                    echo $input;
                    break;

                case 'array':
                    echo $input['html'];
                    break;
            }
            ?>
            </div>
            <?php $embeddedDirty = false; ?>
        <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>