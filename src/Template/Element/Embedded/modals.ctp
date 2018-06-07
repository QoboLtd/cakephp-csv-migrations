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
<?php foreach ($fields as $field) : ?>
    <?php
    $rpos = strrpos($field['name'], '.');
    $fieldName = substr($field['name'], $rpos + 1);
    list($plugin, $controller) = pluginSplit(substr($field['name'], 0, $rpos));

    $modalBody = null;
    try {
        $modalBody = $this->requestAction(
            ['plugin' => $plugin, 'controller' => $controller, 'action' => 'add'],
            [
                'environment' => ['REQUEST_METHOD' => 'GET'],
                'query' => ['embedded' => $controller, 'foreign_key' => $fieldName]
            ]
        );
    } catch (Exception $e) {
        // do nothing
    }

    if (is_null($modalBody)) {
        continue;
    }
    ?>
<!-- Modal -->
<div id="<?= $fieldName ?>_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body"><?= $modalBody ?></div>
        </div>
    </div>
</div>
<?php endforeach; ?>
