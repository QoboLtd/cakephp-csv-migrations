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

use Cake\Utility\Hash;
use CsvMigrations\Utility\FileUpload;

$uuid = $this->Text->uuid();
$limit = 3;
?>
<div class="row">
<?php if ($limit < $entities->count()) : ?>
    <div class="col-xs-12 thumbnail-controls">
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
    <div class="col-xs-4 thumbnail-<?= $index ?>">
        <a href="<?= $entity->get('path') ?>" class="lightbox-image" data-fancybox="photos-<?= $uuid ?>">
            <div class="thumbnail" title="<?= $entity->get('filename') ?>">
                <?= $this->Html->image(Hash::get(
                    $entity->get('thumbnails'),
                    in_array($entity->extension, FileUpload::IMAGE_EXTENSIONS) ? 'small' : 'large'
                )) ?>
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
<?php
if ($limit < $entities->count()) {
    echo $this->Html->scriptBlock(
        '$("a[href=\'#collapseFiles' . $uuid . '\']").click(function () {
            $(this).children().hasClass("dropdown") ?
                $(this).children().removeClass("dropdown").addClass("dropup") :
                $(this).children().removeClass("dropup").addClass("dropdown");
        });',
        ['block' => 'scriptBottom']
    );
}
