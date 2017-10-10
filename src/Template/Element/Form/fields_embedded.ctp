<?php
$embeddedDirty = false;
$embeddedFields = [];
foreach ($fields as $panelFields) {
    foreach ($panelFields as $subFields) {
        foreach ($subFields as $field) {
            if ('' === trim($field['name'])) {
                continue;
            }
            // embedded field
            if ('EMBEDDED' === $field['name']) {
                $embeddedDirty = true;
                continue;
            }

            if ($embeddedDirty) {
                $embeddedFields[] = $field;
            }

            $embeddedDirty = false;
        }
    }
}

if (!empty($embeddedFields)) {
    echo $this->element('CsvMigrations.Embedded/modals', [
        'fields' => $embeddedFields
    ]);
}
