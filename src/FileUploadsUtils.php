<?php
namespace CsvMigrations;

use Burzum\FileStorage\Storage\StorageManager;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\ORM\Table as UploadTable;

class FileUploadsUtils
{
    /**
     * Files database table name
     */
    const TABLE_FILES = 'files';

    /**
     * File-Storage database table name
     */
    const TABLE_FILE_STORAGE = 'file_storage';

    /**
     * One-to-many association identifier
     */
    const ASSOCIATION_ONE_TO_MANY_ID = 'oneToMany';

    /**
     * Many-to-one association identifier
     */
    const ASSOCIATION_MANY_TO_ONE_ID = 'manyToOne';

    /**
     * Instance of Cake ORM Table
     * @var \Cake\ORM\Table
     */
    protected $_table;

    /**
     * Instance of a Files Association class
     *
     * @var \Cake\ORM\Association
     */
    protected $_fileAssociation;

    /**
     * Instance of File-Storage Association class
     *
     * @var \Cake\ORM\Association
     */
    protected $_fileStorageAssociation;

    /**
     * Document foreign key for many-to-many association with Files
     *
     * @var string
     */
    protected $_documentForeignKey;

    /**
     * File foreign key for many-to-many association with Documents
     *
     * @var string
     */
    protected $_fileForeignKey;

    /**
     * File-Storage table foreign key
     *
     * @var string
     */
    protected $_fileStorageForeignKey;

    /**
     * Image file extensions
     *
     * @var array
     */
    protected $_imgExtensions = ['jpg', 'png', 'jpeg', 'gif'];

    /**
     * Contructor method
     *
     * @param UploadTable $table Upload Table Instance
     */
    public function __construct(UploadTable $table)
    {
        $this->_table = $table;

        $this->_getFileAssociationInstance();
        $this->_getDocumentForeignKey();
        $this->_getFileForeignKey();

        $this->_getFileStorageAssociationInstance();
        $this->_fileStorageForeignKey = 'foreign_key';
    }

    /**
     * Get instance of Files association.
     *
     * @return void
     */
    protected function _getFileAssociationInstance()
    {
        foreach ($this->_table->associations() as $association) {
            // @todo temporary performance fix
            if (ucfirst(static::TABLE_FILES) === $association->className()) {
                $this->_fileAssociation = $association;
                break;
            }
        }
    }

    /**
     * Get Document foreign key in many-to-many association with Files.
     *
     * @return void
     */
    protected function _getDocumentForeignKey()
    {
        if (!is_null($this->_fileAssociation)) {
            $this->_documentForeignKey = $this->_fileAssociation->foreignKey();
        }
    }

    /**
     * Get File foreign key in many-to-one association with FileStorage.
     *
     * @return void
     */
    protected function _getFileForeignKey()
    {
        if (is_null($this->_fileAssociation)) {
            return;
        }

        foreach ($this->_fileAssociation->associations() as $association) {
            if (static::TABLE_FILE_STORAGE !== $association->target()->table()) {
                continue;
            }

            if (static::ASSOCIATION_MANY_TO_ONE_ID !== $association->type()) {
                continue;
            }

            $this->_fileForeignKey = $association->foreignKey();
        }
    }

    /**
     * Get instance of FileStorage association.
     *
     * @return void
     */
    protected function _getFileStorageAssociationInstance()
    {
        if (is_null($this->_fileAssociation)) {
            return;
        }

        foreach ($this->_fileAssociation->associations() as $association) {
            if ($this->_fileForeignKey === $association->foreignKey()) {
                $this->_fileStorageAssociation = $association;
                break;
            }
        }
    }

    /**
     * Get files by foreign key record.
     *
     * @param  string              $data  Record id
     * @return \Cake\ORM\ResultSet
     */
    public function getFiles($data)
    {
        $query = $this->_fileStorageAssociation->find('all', [
            'conditions' => [$this->_fileStorageForeignKey => $data]
        ]);

        return $query->all();
    }

    /**
     * File save method.
     *
     * @param  \Cake\ORM\Entity $entity Associated Entity
     * @param  array            $files  Uploaded files
     * @return bool
     */
    public function save(Entity $entity, array $files = [])
    {
        $result = false;

        if (empty($files)) {
            return $result;
        }

        foreach ($files as $file) {
            // file not stored and not uploaded.
            if ($this->_isInValidUpload($file['error'])) {
                continue;
            }

            $fsEntity = $this->_storeFileStorage($entity, ['file' => $file]);
            if ($fsEntity) {
                $result = $this->_storeFile($entity, $fsEntity, $file);
            }
        }

        return $result;
    }

    /**
     * Store to FileStorage table.
     *
     * @param  object $docEntity Document entity
     * @param  array $fileData File data
     * @return object|bool Fresh created entity or false on unsuccesful attempts.
     */
    protected function _storeFileStorage($docEntity, $fileData)
    {
        $fileStorEnt = $this->_fileStorageAssociation->newEntity($fileData);
        $fileStorEnt = $this->_fileStorageAssociation->patchEntity(
            $fileStorEnt,
            [$this->_fileStorageForeignKey => $docEntity->get('id')]
        );

        if ($this->_fileStorageAssociation->save($fileStorEnt)) {
            $this->_createThumbnails($fileStorEnt);

            return $fileStorEnt;
        }

        return false;
    }

    /**
     * Store file entity.
     *
     * @param  object $docEntity Document entity
     * @param  object $fileStorEnt FileStorage entity
     * @return object|bool
     */
    protected function _storeFile($docEntity, $fileStorEnt)
    {
        $entity = $this->_fileAssociation->newEntity([
            $this->_documentForeignKey => $docEntity->get('id'),
            $this->_fileForeignKey => $fileStorEnt->get('id'),
        ]);

        return $this->_fileAssociation->save($entity);
    }

    /**
     * File delete method.
     *
     * @param  string $id Associated Entity id
     * @return bool
     */
    public function delete($id)
    {
        $result = $this->_deleteFileAssociationRecord($id);

        if ($result) {
            $result = $this->_deleteFileRecord($id);
        }

        return $result;
    }

    /**
     * Method that fetches and deletes document-file manyToMany association record Entity.
     *
     * @param  string $id file id
     * @return bool
     */
    protected function _deleteFileAssociationRecord($id)
    {
        if (is_null($this->_fileAssociation)) {
            return false;
        }

        $query = $this->_fileAssociation->find('all', [
            'conditions' => [$this->_fileForeignKey => $id]
        ]);
        $entity = $query->first();

        if (is_null($entity)) {
            return false;
        }

        return $this->_fileAssociation->delete($entity);
    }

    /**
     * Method that fetches and deletes file Entity.
     *
     * @param  string $id file id
     * @return bool
     */
    protected function _deleteFileRecord($id)
    {
        $entity = $this->_fileStorageAssociation->get($id);

        $result = $this->_fileStorageAssociation->delete($entity);

        if ($result) {
            $this->_removeThumbnails($entity);
        }

        return $result;
    }

    /**
     * Method used for creating image file thumbnails.
     *
     * @param  \Cake\ORM\Entity $entity File Entity
     * @return void
     */
    protected function _createThumbnails(Entity $entity)
    {
        $this->_handleThumbnails($entity, 'ImageVersion.createVersion');
    }

    /**
     * Method used for removing image file thumbnails.
     *
     * @param  \Cake\ORM\Entity $entity File Entity
     * @return void
     */
    protected function _removeThumbnails(Entity $entity)
    {
        $this->_handleThumbnails($entity, 'ImageVersion.removeVersion');
    }

    /**
     * Method used for handling image file thumbnails creation and removal.
     *
     * Note that the code on this method was borrowed fromBurzum/FileStorage
     * plugin, ImageVersionShell Class _loop method.
     *
     * @param  \Cake\ORM\Entity $entity    File Entity
     * @param  string           $eventName Event name
     * @return void
     */
    protected function _handleThumbnails(Entity $entity, $eventName)
    {
        if (!in_array($entity->extension, $this->_imgExtensions)) {
            return;
        }

        $operations = Configure::read('FileStorage.imageSizes.' . static::TABLE_FILE_STORAGE);
        $storageTable = TableRegistry::get('Burzum/FileStorage.ImageStorage');
        foreach ($operations as $version => $operation) {
            $payload = [
                'record' => $entity,
                'storage' => StorageManager::adapter($entity->adapter),
                'operations' => [$version => $operation],
                'versions' => [$version],
                'table' => $storageTable,
                'options' => []
            ];

            $event = new Event($eventName, $storageTable, $payload);
            EventManager::instance()->dispatch($event);
        }
    }

    /**
     * Checks if the file is invalid from its error code.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @param  int  $error PHP validation error
     * @return bool true for invalid.
     */
    protected function _isInValidUpload($error)
    {
        return (bool)$error;
    }
}
