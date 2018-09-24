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
$closed = true;

$thumbnailPath = function ($entity) use ($extensions, $hashes, $imageSize) {
    if (! in_array($entity->get('extension'), $extensions)) {
        return 'Qobo/Utils.thumbnails/no-thumbnail.jpg';
    }

    if (! isset($hashes[$imageSize])) {
        return $entity->get('path');
    }

    // image directory path
    $path = realpath(WWW_ROOT . trim($entity->get('path'), DS));
    // image version directory path
    $path = dirname($path) . DS . basename($path, $entity->get('extension'));
    $path .= $hashes[$imageSize] . '.' . $entity->get('extension');

    if (! file_exists($path)) {
        return $entity->get('path');
    }

    return implode('', [
        dirname($entity->get('path')) . '/',
        basename($entity->get('path'), $entity->get('extension')),
        $hashes[$imageSize] . '.' . $entity->get('extension')
    ]);
};
?>
<?php if ($limit < $entities->count()) : ?>
    <p class="text-right">
        <a href="#collapseFiles<?= $uuid ?>" class="btn btn-default btn-xs" data-toggle="collapse" aria-controls="collapseFiles<?= $uuid ?>" aria-expanded="false" role="button">
            <span class="dropdown">
                <span class="caret"></span>
            </span>
        </a>
    </p>
<?php endif; ?>
<?php foreach ($entities as $index => $entity) : ?>
    <?php if ($limit < $entities->count() && $limit === $index) : ?>
        <div class="collapse" id="collapseFiles<?= $uuid ?>">
    <?php endif; ?>
    <?php if (is_int($index / $limit)) : ?>
        <?php $closed = false; ?>
        <div class="row">
    <?php endif; ?>
    <div class="col-xs-4">
        <a href="<?= $entity->get('path') ?>" target="_blank">
            <div class="thumbnail" title="<?= $entity->get('filename') ?>" height="150">
                <?= $this->Html->image($thumbnailPath($entity)) ?>
                <div class="caption">
                    <p class="small text-center no-margin" style="white-space: nowrap; text-overflow: ellipsis;overflow: hidden;">
                        <?= $entity->get('filename') ?>
                    </p>
                </div>
            </div>
        </a>
    </div>
    <?php if (is_int(($index + 1) / $limit)) : ?>
        <?php $closed = true; ?>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
<?php if (! $closed) : ?>
    </div>
<?php endif; ?>
<?php if ($limit < $entities->count()) : ?>
    </div>
<?php endif; ?>
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
