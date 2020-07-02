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

namespace CsvMigrations\Utility;

use Burzum\FileStorage\Model\Entity\FileStorage;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Log\LogTrait;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\View\Helper\UrlHelper;
use Cake\View\View;
use CsvMigrations\Event\EventName;
use Qobo\Utils\Module\Exception\MissingModuleException;
use Qobo\Utils\Module\ModuleRegistry;
use Qobo\Utils\Utility;
use Webmozart\Assert\Assert;

final class FileUpload
{
    use LogTrait;

    /**
     * FileStorage table name.
     */
    const FILE_STORAGE_TABLE_NAME = 'Burzum/FileStorage.FileStorage';

    /**
     * Supported field types.
     */
    const FIELD_TYPES = ['files'];

    /**
     * FileStorage table foreign key.
     */
    const FILE_STORAGE_FOREIGN_KEY = 'foreign_key';

    /**
     * Image file extensions.
     *
     * @var string[]
     */
    const IMAGE_EXTENSIONS = ['jpg', 'png', 'jpeg', 'gif', 'jfif'];

    /**
     * Table instance.
     *
     * @var \Cake\ORM\Table
     */
    private $table;

    /**
     * FileStorage table instance.
     *
     * @var \Cake\Datasource\RepositoryInterface
     */
    private $storageTable;

    /**
     * UrlHelper instance.
     *
     * @var \Cake\View\Helper\UrlHelper|null
     */
    private $urlHelper = null;

    /**
     * Contructor method.
     *
     * @param \Cake\ORM\Table $table Table Instance
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->storageTable = TableRegistry::get(self::FILE_STORAGE_TABLE_NAME);

        /**
         * NOTE: if we don't have a predefined setup for the field image
         * versions, we add it dynamically with default thumbnail versions.
         */
        if (empty((array)Configure::read('FileStorage.imageSizes.' . $table->getTable()))) {
            Configure::write('FileStorage.imageSizes.' . $table->getTable(), Configure::read('ThumbnailVersions'));
        }
    }

    /**
     * Get files by foreign key record.
     *
     * @param string $field Field name
     * @param string $id Foreign key value (UUID)
     * @param mixed[] $orderBy Order by fields and direction
     * @return \Cake\Datasource\ResultSetInterface
     */
    public function getFiles(string $field, string $id, array $orderBy = []): ResultSetInterface
    {
        $query = $this->storageTable->find('all')
            ->where([self::FILE_STORAGE_FOREIGN_KEY => $id, 'model' => $this->table->getTable(), 'model_field' => $field]);

        if (0 < count($orderBy)) {
            $query->order($orderBy);
        } else {
            $query->order($this->getOrderClause($field));
        }

        $result = $query->all();
        foreach ($result as $entity) {
            $entity = $this->attachThumbnails($entity);
        }

        return $result;
    }

    /**
     * Adds order clause to the provided Query based on specified field configuration.
     *
     * @see  https://github.com/QoboLtd/cakephp-utils/blob/v9.2.0/src/ModuleConfig/Parser/Schema/fields.json#L30-L40
     * @param string $field Field name
     * @return mixed[]
     */
    private function getOrderClause(string $field): array
    {
        $className = App::shortName(get_class($this->table), 'Model/Table', 'Table');
        $config = [];
        try {
            $config = ModuleRegistry::getModule($className)->getFields();
        } catch (MissingModuleException $e) {
            return [];
        }

        if (empty($config[$field]['orderBy'])) {
            return [];
        }

        if (empty($config[$field]['orderDir'])) {
            return [];
        }

        return [$config[$field]['orderBy'] => $config[$field]['orderDir']];
    }

    /**
     * Attaches thumbnails field to FileStorage entity.
     *
     * @param \Burzum\FileStorage\Model\Entity\FileStorage $entity FileStorage entity
     * @return \Burzum\FileStorage\Model\Entity\FileStorage
     */
    private function attachThumbnails(FileStorage $entity): FileStorage
    {
        $entity->set('thumbnails', $this->getThumbnails($entity));

        return $entity;
    }

    /**
     * File storage entity thumbnails getter.
     *
     * @param \Burzum\FileStorage\Model\Entity\FileStorage $entity File storage entity
     * @return string[]
     */
    public function getThumbnails(FileStorage $entity): array
    {
        $versions = (array)Configure::read('FileStorage.imageHashes.file_storage');
        if (empty($versions)) {
            return [];
        }

        $result = [];
        foreach (array_keys($versions) as $version) {
            $result[$version] = $this->getThumbnail($entity, (string)$version);
        }

        return $result;
    }

    /**
     * File storage entity thumbnail url getter by version.
     *
     * @param \Burzum\FileStorage\Model\Entity\FileStorage $entity FileStorage entity
     * @param string $version Version name
     * @return string
     */
    public function getThumbnail(FileStorage $entity, string $version): string
    {
        $versions = (array)Configure::read('FileStorage.imageHashes.file_storage');
        if (empty($versions)) {
            return str_replace(DS, '/', $entity->get('path'));
        }

        if (! array_key_exists($version, $versions)) {
            return str_replace(DS, '/', $entity->get('path'));
        }

        $path = in_array(strtolower($entity->get('extension')), self::IMAGE_EXTENSIONS) ?
            $this->getImagePath($entity, $version) :
            $this->getIconPath($entity, $version);

        return str_replace(DS, '/', $path);
    }

    /**
     * Return the full path of all the files, of selected fields, of a foreign key
     * ex: getUrls('uuid-property','photos','small'), getUrls('uuid-documents','file')
     *
     * @param string $id Foreign key value (UUID)
     * @param string $mediaSource Field value (photos, featured_photo, file, etc..)
     * @param string $mediaSize Size of photo
     * @return mixed[]
     */
    public function getFilesUrls(string $id, string $mediaSource, string $mediaSize = null): array
    {
        if (empty($mediaSource) && !in_array($mediaSource, self::fileFields($this->table->getAlias()))) {
            return [];
        }

        $files = $this->getFiles($mediaSource, $id);
        $thumbs = $files->extract($mediaSize ? 'thumbnails.' . $mediaSize : 'path')->map(function ($thumb) {
            return Router::url($thumb, true);
        })->toArray();

        return $thumbs;
    }

    /**
     * Return all the available sizes for thumbnails
     *
     * @return mixed[]
     */
    public function getThumbnailSizeList(): array
    {
        $thumbnails = [];
        $versions = Configure::read('ThumbnailVersions', []);
        foreach ($versions as $version => $definition) {
            $thumbnails[$version] = sprintf(
                '%s (%d x %d)',
                ucfirst($version),
                $definition['thumbnail']['width'],
                $definition['thumbnail']['height']
            );
        }

        return $thumbnails;
    }

    /**
     * Image path getter.
     *
     * @param \Burzum\FileStorage\Model\Entity\FileStorage $entity FileStorage entity
     * @param string $version Version name
     * @return string
     */
    private function getImagePath(FileStorage $entity, string $version): string
    {
        $hash = (string)Configure::read(sprintf('FileStorage.imageHashes.file_storage.%s', $version));
        if (empty($hash)) {
            return $entity->get('path');
        }

        $event = new Event('ImageVersion.getVersions', $this, [
            'hash' => $hash,
            'image' => $entity,
            'version' => $version,
            'options' => [],
            'pathType' => 'fullPath',
        ]);

        EventManager::instance()->dispatch($event);

        if (! $event->getResult()) {
            return $entity->get('path');
        }

        return file_exists(WWW_ROOT . trim($event->getResult(), DS)) ?
            $event->getResult() :
            $entity->get('path');
    }

    /**
     * Icon path getter.
     *
     * @param \Burzum\FileStorage\Model\Entity\FileStorage $entity FileStorage entity
     * @param string $version Version name
     * @return string
     */
    private function getIconPath(FileStorage $entity, string $version): string
    {
        $imgSizes = (array)Configure::read(sprintf('FileStorage.imageSizes.%s', $entity->get('model')));

        // no image sizes, return default
        if (empty($imgSizes)) {
            return Utility::getFileTypeIcon($entity->get('extension'));
        }

        // sort by size, biggest first and get current version position in the array
        $position = array_search($version, array_keys(Hash::sort($imgSizes, 'width')), true);

        // no position, return default
        if (false === $position) {
            return Utility::getFileTypeIcon($entity->get('extension'));
        }

        $iconSizes = [512, 48, 32, 16];
        // traversing recursively through the icon sizes until it finds the closest one by key position
        $funcGetIconSize = function (int $pos) use ($iconSizes, &$funcGetIconSize) {
            if (! array_key_exists($pos, $iconSizes)) {
                return $funcGetIconSize($pos - 1);
            }

            return $iconSizes[$pos];
        };

        $result = Utility::getFileTypeIcon($entity->get('extension'), sprintf('%dpx', $funcGetIconSize($position)));

        $result = $this->getUrlHelper()->image($result);

        return $result;
    }

    /**
     * UrlHelper getter method.
     *
     * @return \Cake\View\Helper\UrlHelper
     */
    private function getUrlHelper(): UrlHelper
    {
        if (null === $this->urlHelper) {
            $this->urlHelper = new UrlHelper(new View());
        }

        return $this->urlHelper;
    }

    /**
     * Save method
     *
     * Creates FileStorage entities.
     *
     * @param string $field Field name
     * @param mixed[] $files Uploaded files info
     * @return \Burzum\FileStorage\Model\Entity\FileStorage[] $result
     */
    public function saveAll(string $field, array $files): array
    {
        if (empty($files)) {
            return [];
        }

        $result = [];
        foreach ($files as $file) {
            if (! is_array($file)) {
                $this->log(sprintf('Invalid structure structure provided: %s', gettype($file)), 'error');
                continue;
            }

            $entity = $this->save($field, $file);
            if (null === $entity) {
                continue;
            }

            $result[] = $entity;
        }

        return $result;
    }

    /**
     * Save method
     *
     * Creates FileStorage entity.
     *
     * @param string $field Field name
     * @param mixed[] $file Uploaded file info
     * @return \Burzum\FileStorage\Model\Entity\FileStorage|null New entity or null on unsuccesful attempts.
     */
    public function save(string $field, array $file): ?FileStorage
    {
        $required = ['tmp_name', 'error', 'name', 'type', 'size'];

        $diff = array_diff($required, array_keys($file));
        if (! empty($diff)) {
            $this->log(sprintf('Missing the following required parameter(s): %s', implode(',', $diff)), 'error');

            return null;
        }

        if (!$this->isAllowed($file['type'])) {
            $this->log(sprintf('Mine "%s" is not alloed to be uploaded', $file['type']), 'error');

            return null;
        }

        if (0 !== $file['error']) {
            $this->log(sprintf('File upload error code: %s', $file['error']), 'error');

            return null;
        }

        $entity = $this->storageTable->newEntity(['file' => $file]);
        Assert::isInstanceOf($entity, FileStorage::class);

        /**
         * Field foreign_key is not set here because upload does not know
         * anything about the entity it relates to, as it is not yet created.
         *
         * @var \Burzum\FileStorage\Model\Entity\FileStorage
         */
        $entity = $this->storageTable->patchEntity($entity, [
            'model' => $this->table->getTable(),
            'model_field' => $field,
        ]);

        // always store extension in lowercased format
        $entity->set('extension', strtolower($entity->get('extension')));

        if (! $this->storageTable->save($entity)) {
            $this->log(sprintf('Failed to save file with name: %s', $file['name']), 'error');

            return null;
        }

        // generate thumbnails for image files
        if (in_array(strtolower($entity->get('extension')), self::IMAGE_EXTENSIONS)) {
            $event = new Event((string)EventName::CREATE_THUMBNAILS(), $this->table, compact('entity'));
            EventManager::instance()->dispatch($event);
        }

        return $entity;
    }

    /**
     * Compare the mime type with a list of allowed types.
     *
     * @param string $type File mime type.
     * @return bool
     */
    private function isAllowed(string $type): bool
    {
        foreach ((array)Configure::read('FileUpload.allowedMime') as $mime) {
            if (strpos($type, $mime) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Links provided entity with file(s) found in the request data.
     *
     * @param string $id Entity id
     * @param mixed[] $data Request data
     * @return int Returns count of the affected rows.
     */
    public function link(string $id, array $data): int
    {
        $ids = [];
        foreach (self::fileFields($this->table->getAlias()) as $field) {
            $ids = array_merge($ids, $this->getFileIdsByField($data, $field));
        }

        if (empty($ids)) {
            return 0;
        }

        return $this->storageTable->updateAll(
            [self::FILE_STORAGE_FOREIGN_KEY => $id],
            ['id IN' => $ids]
        );
    }

    /**
     * Returns true if the provided model has files, false otherwise.
     *
     * @param string $modelName Model name
     * @return bool
     */
    public static function hasFileFields(string $modelName): bool
    {
        return [] !== self::fileFields($modelName);
    }

    /**
     * File-type fields getter.
     *
     * @param string $modelName Model name
     * @return mixed[]
     */
    public static function fileFields(string $modelName): array
    {
        $fields = ModuleRegistry::getModule($modelName)->getMigration();

        $fields = array_filter($fields, function ($params) {
            return in_array($params['type'], self::FIELD_TYPES);
        });

        return array_keys($fields);
    }

    /**
     * Retrieves file(s) id from provided request data.
     * Expected formats of request data:
     * - Articles.photos_ids
     * -photos_ids
     *
     * @param mixed[] $data Request data
     * @param string $field Field name
     * @return mixed[]
     */
    private function getFileIdsByField(array $data, string $field): array
    {
        $result = Hash::extract($data, sprintf('%s.%s_ids', $this->table->getAlias(), $field));
        $result = empty($result) ? Hash::extract($data, sprintf('%s_ids', $field)) : $result;
        $result = array_filter((array)$result);

        return $result;
    }

    /**
     * File delete method.
     *
     * @param string $id Associated Entity id
     * @return bool
     * @todo seems like this code is no longer in use, even though it should, as it handles thumbnails removal.
     */
    public function delete(string $id): bool
    {
        $query = $this->storageTable->find('all', [
            'conditions' => [self::FILE_STORAGE_FOREIGN_KEY => $id],
        ]);
        $entity = $query->first();

        if (is_null($entity)) {
            return false;
        }

        if ($this->storageTable->delete($entity)) {
            $event = new Event((string)EventName::REMOVE_THUMBNAILS(), $this->table, compact('entity'));
            EventManager::instance()->dispatch($event);

            return true;
        }

        return false;
    }

    /**
     * Method used for creating image file thumbnails.
     *
     * @param \Burzum\FileStorage\Model\Entity\FileStorage $entity FileStorage entity
     * @return bool
     */
    public function createThumbnails(FileStorage $entity): bool
    {
        return $this->handleThumbnails($entity, 'ImageVersion.createVersion');
    }

    /**
     * Method used for removing image file thumbnails.
     *
     * @param \Burzum\FileStorage\Model\Entity\FileStorage $entity FileStorage entity
     * @return bool
     */
    public function removeThumbnails(FileStorage $entity): bool
    {
        return $this->handleThumbnails($entity, 'ImageVersion.removeVersion');
    }

    /**
     * Method used for handling image file thumbnails creation and removal.
     *
     * Note that the code on this method was borrowed fromBurzum/FileStorage
     * plugin, ImageVersionShell Class _loop method.
     *
     * @param \Burzum\FileStorage\Model\Entity\FileStorage $entity FileStorage entity
     * @param string $eventName Event name
     * @return bool
     */
    private function handleThumbnails(FileStorage $entity, string $eventName): bool
    {
        if (! in_array(strtolower($entity->get('extension')), self::IMAGE_EXTENSIONS)) {
            return false;
        }

        $imgSizes = (array)Configure::read(sprintf('FileStorage.imageSizes.%s', $entity->get('model')));

        if (empty($imgSizes)) {
            $this->log(
                sprintf('Failed to %s: no image sizes defined for model "%s"', $eventName, $entity->get('model')),
                'warning'
            );

            return false;
        }

        $event = new Event($eventName, $this, [
            'entity' => $entity,
            'versions' => array_keys($imgSizes),
        ]);
        EventManager::instance()->dispatch($event);

        $eventResult = $event->getResult();
        if (empty($eventResult)) {
            $this->log(sprintf('Failed to %s: event result is empty', $eventName), 'error');

            return false;
        }

        $result = true;
        foreach (array_keys($imgSizes) as $version) {
            if (! array_key_exists($version, $eventResult)) {
                $result = false;
                $this->log(sprintf('Failed to %s for version "%s"', $eventName, $version), 'error');
            }

            if ('error' === $eventResult[$version]['status']) {
                $result = false;
                $this->log(sprintf('Failed to handle thumbnail: %s', $eventResult[$version]['error']), 'error');
            }
        }

        return $result;
    }
}
