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

$embeddedFields = [];
$associationFields = [];

foreach ($fields as $panelFields) {
    foreach ($panelFields as $subFields) {
        foreach ($subFields as $field) {
            if ('' === trim($field['name'])) {
                continue;
            }

            // embedded field detection
            preg_match(CsvField::PATTERN_TYPE, $field['name'], $matches);

            if (empty($matches[1]) || !in_array($matches[1], ['EMBEDDED','ASSOCIATION'])) {
                continue;
            }

            $field['name'] = $matches[2];
            $field['type'] = $matches[1];

            if ('EMBEDDED' === $matches[1]) {
                $embeddedFields[] = $field;
            }else{
                $associationFields[] = $field;
            }
            
        }
    }
}

if (!empty($embeddedFields)) {
    echo $this->element('CsvMigrations.Embedded/modals', [
        'fields' => $embeddedFields
    ]);
}

if (!empty($associationFields)) {
    echo $this->element('CsvMigrations.Associated/modals', [
        'fields' => $associationFields
    ]);
}
