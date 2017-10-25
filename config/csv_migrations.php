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
        'actions' => ['add', 'edit', 'index', 'view'],
        'batch' => [
            'active' => true,
            'button_id' => '#batch-button',
            'action' => 'batch',
            'types' => ['datetime', 'time', 'date', 'reminder', 'string', 'text', 'list', 'email', 'phone', 'url', 'boolean', 'related']
        ],
        'panels' => [
            // actions to arrange fields into panels
            'actions' => ['add', 'batch', 'edit', 'view'],
            // actions that require dynamic panel functionality
            'dynamic_actions' => ['add', 'edit']
        ],
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
