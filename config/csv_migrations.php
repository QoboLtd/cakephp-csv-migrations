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
            'types' => ['datetime', 'time', 'date', 'reminder', 'string', 'text', 'list', 'email', 'phone', 'url', 'boolean', 'related'],
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
                'validateInitialCount' => true,
            ],
            'initialPreviewConfig' => [
                'url' => "/api/file-storages/delete/",
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
        "coordinates_field" => [
            "GoogleMapKey" => "",
            "DefaultGPSLocation" => "35.151012309561715,33.3649206161499",
        ],
    ],
];
