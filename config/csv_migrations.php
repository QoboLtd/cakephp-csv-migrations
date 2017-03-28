<?php
// get upload limit in bytes
$uploadLimit = sizeToBytes(ini_get('upload_max_filesize'));

// CsvMigrations plugin configuration
return [
    'CsvMigrations' => [
        'api' => [
            'auth' => true,
            'token' => null
        ],
        'acl' => [
            'class' => null, // currently only accepts Table class with prefixed plugin name. Example: 'MyPlugin.TableName'
            'method' => null,
            'component' => null
        ],
        'actions' => ['index', 'view', 'add', 'edit'],
        'modules' => [
            'path' => CONFIG . 'Modules' . DS
        ],
        'reports' => [
            'filename' => 'reports'
        ],
        'default_icon' => 'cube',
        'select2' => [
            'min_length' => 0,
            'timeout' => 300,
            'id' => '[data-type="select2"]',
            'limit' => 10
        ],
        // bootstrap-fileinput configuration
        // link: https://github.com/kartik-v/bootstrap-fileinput
        'BootstrapFileInput' => [
            'defaults' => [
                'showUpload' => true,
                'showRemove' => false,
                'showUploadedThumbs' => true,
                'uploadAsync' => true,
                'dropZoneEnabled' => false,
                'fileActionSettings' => [
                    'showUpload' => false,
                    'showZoom' => false,
                ],
                'maxFileCount' => 30,
                'fileSizeGetter' => true,
                // this should always be set in kilobytes
                'maxFileSize' => (int)($uploadLimit / 1024),
            ],
            'initialPreviewConfig' => [
                'url' => "/api/file-storages/delete/"
            ]
        ]
    ]
];
