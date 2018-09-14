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

use CsvMigrations\FieldHandlers\CsvField;

$value = '&nbsp;';
if ('' !== trim($field['name'])) {
    $handlerOptions = $options['handlerOptions'];

    // embedded field detection
    preg_match(CsvField::PATTERN_TYPE, $field['name'], $matches);
    if (! empty($matches[1]) && 'EMBEDDED' === $matches[1]) {
        $handlerOptions['embeddedModal'] = true;
        $field['name'] = explode('.', $matches[2]);
        $field['name'] = end($field['name']);
    }

    // non-embedded field
    $tableName = $field['model'];
    if (! is_null($field['plugin'])) {
        $tableName = $field['plugin'] . '.' . $tableName;
    }

    // get data from entity
    $data = $options['entity']->get($field['name']);
    if (! $data) {
        // allowing query params to define field values.
        if ($this->request->query($field['name'])) {
            $data = $this->request->query($field['name']);
        }

        if ($this->request->data($field['name'])) {
            $data = $this->request->data($field['name']);
        }
    }

    $input = $factory->renderInput($tableName, $field['name'], $data, $handlerOptions);

    switch (gettype($input)) {
        case 'string':
            $value = $input;
            break;

        case 'array':
            $value = $input['html'];
            break;
    }
}

// calculate column width
$columnWidth = (int)floor(12 / $fieldCount);
$columnWidth = 6 < $columnWidth ? 6 : $columnWidth; // max-supported input size is half grid
?>
<div class="col-xs-12 col-md-<?= $columnWidth ?> field-wrapper"><?= $value ?></div>