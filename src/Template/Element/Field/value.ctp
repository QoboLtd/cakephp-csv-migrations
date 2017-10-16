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

$tableName = $field['model'];
if ($field['plugin']) {
    $tableName = $field['plugin'] . '.' . $tableName;
}

$renderOptions = ['entity' => $options['entity'], 'imageSize' => 'small'];

$label = $factory->renderName($tableName, $field['name'], $renderOptions);
$value = $factory->renderValue($tableName, $field['name'], $options['entity'], $renderOptions);
?>
<div class="col-xs-4 col-md-2 text-right"><strong><?= $label ?>:</strong></div>
<div class="col-xs-8 col-md-4"><?= !empty($value) ? $value : '&nbsp;' ?></div>