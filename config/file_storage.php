<?php
use Burzum\FileStorage\Lib\FileStorageUtils;
use Burzum\FileStorage\Storage\Listener\BaseListener;
use Burzum\FileStorage\Storage\StorageManager;
use Cake\Core\Configure;
use Cake\Event\EventManager;

/*
    default thumbnail setup for all
    $entity->model entities for file_storage
*/
Configure::write('ThumbnailVersions', [
    'huge' => [
        'thumbnail' => ['width' => 2000, 'height' => 2000]
    ],
    'large' => [
        'thumbnail' => ['width' => 1024, 'height' => 1024]
    ],
    'medium' => [
        'thumbnail' => ['width' => 500, 'height' => 500]
    ],
    'small' => [
        'thumbnail' => ['width' => 150, 'height' => 150]
    ],
    'tiny' => [
        'thumbnail' => ['width' => 50, 'height' => 50]
    ]
]);



Configure::write('FileStorage', [
    'pathBuilderOptions' => ['pathPrefix' => 'uploads'],
    'association' => 'UploadDocuments',
    'imageSizes' => [
        'file_storage' => [
            'huge' => [
                'thumbnail' => ['width' => 2000, 'height' => 2000]
            ],
            'large' => [
                'thumbnail' => ['width' => 1024, 'height' => 1024]
            ],
            'medium' => [
                'thumbnail' => ['width' => 500, 'height' => 500]
            ],
            'small' => [
                'thumbnail' => ['width' => 150, 'height' => 150]
            ],
            'tiny' => [
                'thumbnail' => ['width' => 50, 'height' => 50]
            ]
        ]
    ]
]);

/**
 * @todo  find if we need this or not
 */
FileStorageUtils::generateHashes();

StorageManager::config(
    'Local',
    [
        'adapterOptions' => [WWW_ROOT, true],
        'adapterClass' => '\Gaufrette\Adapter\Local',
        'class' => '\Gaufrette\Filesystem'
    ]
);
$listener = new BaseListener([
    'pathBuilderOptions' => Configure::read('FileStorage.pathBuilderOptions')
]);
EventManager::instance()->on($listener);
