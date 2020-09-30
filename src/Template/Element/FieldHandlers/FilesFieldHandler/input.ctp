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

use Cake\Core\Configure;
use Qobo\Utils\Module\ModuleRegistry;

$attributes = isset($attributes) ? $attributes : [];

$class = str_replace('.', '_', $name . '_ids');

$config = ModuleRegistry::getModule($this->name)->getFields();

$options = [
    'multiple' => true,
    'data-upload-url' => sprintf("/api/%s/upload", $table),
];

$orderField = Configure::read('CsvMigrations.BootstrapFileInput.orderField');

if (isset($config[$field]['orderBy']) && $orderField == $config[$field]['orderBy'] && isset($config[$field]['orderDir'])) {
    $options['data-file-order'] = 1;
}

if (isset($config[$field]['limit'])) {
    $options['data-file-limit'] = $config[$field]['limit'];
}

if ($value && $entities && $entities->count()) {
    $options['data-document-id'] = $value;
    $files = [];

    foreach ($entities as $entity) {
        switch ($entity->mime_type) {
            case 'application/pdf':
                $previewType = 'pdf';
                break;
            //doc
            case 'application/msword':
            //docx
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            //xls
            case 'application/vnd.ms-excel':
            //xlsx
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            //ppt
            case 'application/vnd.ms-powerpoint':
            //pptx
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                $previewType = 'object';
                break;
            default:
                $previewType = 'image';
                break;
        }

        $files[] = [
            'id' => $entity->id,
            'path' => $entity->path,
            'size' => $entity->get('filesize'),
            'caption' => h($entity->filename),
            'type' => $previewType,
            'file_type' => $entity->mime_type,
        ];
    }
    //passed to generate previews
    $options['data-files'] = json_encode($files);
}
$attributes += $options;
?>
<div <?= $this->Html->help($help); ?> class="form-group <?= $required ? 'required' : '' ?> <?= $this->Form->isFieldError($name) ? 'has-error' : '' ?>">
<?= $this->Form->label($name . '[]', $label) ?>
<?= $this->Form->{$type}($name . '[]', $attributes); ?>
<?php if ($entities && $entities->count()) : ?>
    <?php foreach ($entities as $entity) : ?>
        <?= $this->Form->hidden(
            $name . '_ids[]',
            [
                'class' => $class,
                'value' => $entity->id
            ]
        ); ?>
    <?php endforeach; ?>
<?php else : ?>
    <?= $this->Form->hidden(
        $name . '_ids[]',
        [
            'class' => $class,
            'value' => ''
        ]
    ); ?>
<?php endif; ?>
<?php echo $this->Form->error($name) ?>
</div>
