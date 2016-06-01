<?php
use Burzum\FileStorage\Storage\Listener\BaseListener;
use Burzum\FileStorage\Storage\StorageManager;
use Cake\Core\Configure;
use Cake\Event\EventManager;

Configure::write('FileStorage', [
    'pathBuilderOptions' => ['pathBuilderOptions' => ['pathPrefix' => '/uploads']],
    'association' => 'UploadDocuments',
]);

StorageManager::config(
    'Local',
    [
        'adapterOptions' => [WWW_ROOT, true],
        'adapterClass' => '\Gaufrette\Adapter\Local',
        'class' => '\Gaufrette\Filesystem'
    ]
);
$listener = new BaseListener(Configure::read('FileStorage.pathBuilderOptions'));
EventManager::instance()->on($listener);
