<?php
// get upload limit in bytes
$uploadLimit = Qobo\Utils\Utility\Convert::valueToBytes(ini_get('upload_max_filesize'));

// CsvMigrations plugin configuration
return [
    'CsvMigrations' => [
        /**
         * Enables/disables Table default validation rules.
         *
         * @see \CsvMigrations\Table::validationDefault();
         * @see \CsvMigrations\FieldHandlers\FieldHandler::setValidationRules();
         * @see \CsvMigrations\FieldHandlers\FieldHandlerFactory::setValidationRules();
         * @see src/FieldHandlers/Provider/ValidationRules;
         */
        'tableValidation' => true,
        'api' => [
            'auth' => true,
            'token' => null,
        ],
        'actions' => ['add', 'edit', 'index', 'view'],
        'batch' => [
            'active' => true,
            'button_id' => '#batch-button',
            'action' => 'batch',
            'types' => ['datetime', 'time', 'date', 'reminder', 'string', 'text', 'list', 'email', 'phone', 'url', 'boolean', 'related', 'color'],
        ],
        'panels' => [
            // actions to arrange fields into panels
            'actions' => ['add', 'batch', 'edit', 'view'],
            // actions that require dynamic panel functionality
            'dynamic_actions' => ['add', 'edit'],
        ],
        'modules' => [
            'path' => CONFIG . 'Modules' . DS,
        ],
        'features' => [
            'module' => [
                'path' => APP . 'Feature' . DS . 'Type' . DS . 'Module',
                'path_fragment' => 'Feature' . DS . 'Type' . DS . 'Module' . DS,
                'template' => 'Feature/feature',
            ],
        ],
        'reports' => [
            'filename' => 'reports',
        ],
        'default_icon' => 'cube',
        'select2' => [
            'min_length' => 0,
            'timeout' => 300,
            'id' => '[data-type="select2"]',
            'limit' => 10,
        ],
        'appView' => \CsvMigrations\View\AppView::class,
        // TinyMCE plugin configuration
        'TinyMCE' => [
            'selector' => 'textarea.tinymce',
            'relative_urls' => false,
            'plugins' => ['link'],
            'menubar' => false,
            'toolbar' => 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent link',
            'browser_spellcheck' => true,
            'file_browser_callback_types' => '',
            'theme' => 'modern',
            'height' => 300,
        ],
        // bootstrap-fileinput configuration
        // link: https://github.com/kartik-v/bootstrap-fileinput
        'BootstrapFileInput' => [
            'defaults' => [
                'theme' => 'explorer',
                'showCaption' => false,
                'showRemove' => false,
                'showUploadedThumbs' => false,
                'reversePreviewOrder' => false,
                'uploadAsync' => true,
                'dropZoneEnabled' => false,
                'browseOnZoneClick' => true,
                'fileActionSettings' => [
                    'showUpload' => false,
                    'showZoom' => false,
                    'showDrag' => true,
                ],
                'maxFileCount' => 30,
                'fileSizeGetter' => true,
                // this should always be set in kilobytes
                'maxFileSize' => (int)($uploadLimit / 1024),
                'validateInitialCount' => true,
                'allowedFileTypes' => [],
                //Array of types or false to show preview for all
                'allowedPreviewTypes' => ['image'],
                'initialPreviewFileType' => 'image',
                'preferIconicPreview' => false,
                'previewFileIconSettings' => [
                    'doc' => '<i class="fa fa-file-word-o text-primary"></i>',
                    'docx' => '<i class="fa fa-file-word-o text-primary"></i>',
                    'xls' => '<i class="fa fa-file-excel-o text-success"></i>',
                    'xlsx' => '<i class="fa fa-file-excel-o text-success"></i>',
                    'ppt' => '<i class="fa fa-file-powerpoint-o text-danger"></i>',
                    'pptx' => '<i class="fa fa-file-powerpoint-o text-danger"></i>',
                    'jpg' => '<i class="fa fa-file-photo-o text-warning"></i>',
                    'png' => '<i class="fa fa-file-photo-o text-warning"></i>',
                    'jfif' => '<i class="fa fa-file-photo-o text-warning"></i>',
                    'pdf' => '<i class="fa fa-file-pdf-o text-danger"></i>',
                    'zip' => '<i class="fa fa-file-archive-o text-muted"></i>',
                ],
            ],
            'initialPreviewConfig' => [
                'url' => "/api/file-storages/delete/",
            ],
            'orderField' => 'order',
            'previewTypes' => [
                'application/pdf' => 'pdf',
                'application/msword' => 'object',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'object',
                'application/vnd.ms-excel' => 'object',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'object',
                'application/vnd.ms-powerpoint' => 'object',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'object',
            ],
        ],
        // Configuration options for the ValidateShell
        'ValidateShell' => [
            // Module-specific configuration options
            'module' => [
                // Default module options (used if no module-specific options given)
                '_default' => [
                    // The list of checks to perform during the module validation.
                    // Checks are an associative array of classes (keys) and options
                    // (values).  Classes have exist and implement the
                    // CsvMigrations\Utility\Validate\Check\CheckInterface or an
                    // exception will be thrown during the validation run.
                    'checks' => [
                        'CsvMigrations\\Utility\\Validate\\Check\\ConfigCheck' => [
                            // List of fields, which are not allowed to be used as
                            // display_field.  For example: id.
                            'display_field_bad_values' => [],
                            // List of icons, which are not allowed to be used as
                            // module icons.  For example: cube.
                            'icon_bad_values' => [],
                        ],
                        'CsvMigrations\\Utility\\Validate\\Check\\FieldsCheck' => [],
                        'CsvMigrations\\Utility\\Validate\\Check\\MenusCheck' => [],
                        'CsvMigrations\\Utility\\Validate\\Check\\ReportsCheck' => [],
                        'CsvMigrations\\Utility\\Validate\\Check\\MigrationCheck' => [],
                        'CsvMigrations\\Utility\\Validate\\Check\\ViewsCheck' => [],
                    ],
                ],
            ],
        ],
        'GoogleMaps' => [
            'ApiKey' => '',
            'DefaultLocation' => '0.0,0.0',
        ],
        'Inputmask' => [
            "setup" => [
                'rightAlign' => false,
                'removeMaskOnSubmit' => true,
                'numericInput' => true,
                'alias' => 'numeric',
                'groupSeparator' => ',',
                'digitsOptional' => false,
                'placeholder' => '0',
            ],
            "Area" => [
                'digits' => 0,
                'suffix' => 'm²',
            ],
            "Distance" => [
                'digits' => 0,
                'suffix' => 'km',
            ],
            "Currency" => [
                'digits' => 2,
                'prefix' => '$',
            ],
        ],
    ],
];
