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

use Cake\Core\Plugin;

$uuid = $this->Text->uuid();
$limit = 3;
?>
<div class="row">
<?php if ($limit < $entities->count()) : ?>
    <div class="col-xs-12">
        <p class="text-right">
            <a href="#collapseFiles<?= $uuid ?>" class="btn btn-default btn-xs" data-toggle="collapse" aria-controls="collapseFiles<?= $uuid ?>" aria-expanded="false" role="button">
                <span class="dropdown">
                    <span class="caret"></span>
                </span>
            </a>
        </p>
    </div>
<?php endif; ?>
<?php foreach ($entities as $index => $entity) : ?>
    <?php if ($limit < $entities->count() && $limit === $index) : ?>
        <div class="collapse" id="collapseFiles<?= $uuid ?>">
    <?php endif; ?>
    <div class="col-xs-4">
        <a href="<?= $entity->get('path') ?>" target="_blank">
            <div class="thumbnail" title="<?= $entity->get('filename') ?>">
                <?php
                $path = Plugin::path('CsvMigrations') . DS . 'webroot' . DS . 'img' . DS . 'icons' . DS;
                $path .= 'files' . DS . '48px' . DS . strtolower($entity->get('extension')) . '.png';
                $filename = file_exists($path) ? strtolower($entity->get('extension')) : '_blank';
                ?>
                <?= $this->Html->image('CsvMigrations.icons/files/48px/' . $filename . '.png') ?>
                <div class="caption">
                    <p class="small text-center no-margin" style="white-space: nowrap; text-overflow: ellipsis;overflow: hidden;">
                        <?= $entity->get('filename') ?>
                    </p>
                </div>
            </div>
        </a>
    </div>
<?php endforeach; ?>
<?php if ($limit < $entities->count()) : ?>
    </div>
<?php endif; ?>
</div>
<?php if ($limit < $entities->count()) {
    echo $this->Html->scriptBlock(
        '$("a[href=\'#collapseFiles' . $uuid . '\']").click(function () {
            $(this).children().hasClass("dropdown") ?
                $(this).children().removeClass("dropdown").addClass("dropup") :
                $(this).children().removeClass("dropup").addClass("dropdown");
        });',
        ['block' => 'scriptBottom']
    );
}
?>
