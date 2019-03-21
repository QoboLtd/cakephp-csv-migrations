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
use Cake\ORM\TableRegistry;

$tableName = $field['model'];
if ($field['plugin']) {
    $tableName = $field['plugin'] . '.' . $tableName;
}

$renderOptions = ['entity' => $options['entity']];

$label = $factory->renderName($tableName, $field['name'], $renderOptions);

if ('' !== trim($field['name'])) {
	// association field detection
    preg_match(CsvField::PATTERN_TYPE, $field['name'], $matches);
    if (! empty($matches[1]) && 'ASSOCIATION' === $matches[1]) {
        $field['name'] = $matches[2];
        //Get table object in order to find the association
        $table = TableRegistry::getTableLocator()->get($field['model']);
        if ($table->hasAssociation($field['name'])) {
        	$renderOptions['embeddedModal'] = true;
            $association = $table->getAssociation($field['name']);
            $renderOptions['association'] = $association;
            $renderOptions['fieldDefinitions']['type'] = 'belongsToMany(' . $association->className() .  ')';
            $label = $association->className();
        }
    }
}


$value = $factory->renderValue($tableName, $field['name'], $options['entity'], $renderOptions);
$value = empty($value) ? '&nbsp;' : $value;

// calculate column width
$columnWidth = (int)floor(12 / $fieldCount);
$columnWidth = 6 < $columnWidth ? 6 : $columnWidth; // max-supported input size is half grid
?>
<?php if (2 >= $fieldCountMax) : // horizontal style ?>
    <div class="col-xs-4 col-md-2 text-right"><strong><?= $label ?>:</strong></div>
    <div class="col-xs-8 col-md-4"><?= $value ?></div>
<?php endif ?>
<?php if (2 < $fieldCountMax) : // default style ?>
    <div class="col-xs-12 col-md-<?= $columnWidth ?>">
        <div class="form-group">
            <label class="control-label"><?= $label ?></label><br />
            <?= $value ?>
        </div>
    </div>
<?php endif ?>