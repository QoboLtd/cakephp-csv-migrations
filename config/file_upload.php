<?php
use Cake\Core\Configure;

/*
    default thumbnail setup for all
    $entity->model entities for file_storage
*/
Configure::write('CsvMigrations.BootstrapFileInput', [
    'defaults' => [
        'showUpload' => true,
        'showRemove' => false,
        'showUploadedThumbs' => true,
        'uploadAsync' => true,
        'dropZoneEnabled' => false,
        'showUploadedThumbs' => false,
        'fileActionSettings' => [
            'showUpload' => false,
            'showZoom' => false,
        ],
        'maxFileCount' => 30,
        'fileSizeGetter' => true,
        'maxFileSize' => 2000,
        'uploadUrl' => "/api/%s/upload"
    ],
    'initialPreviewConfig' => [
        'url' => "/api/file-storages/delete/"
    ]
]);
