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
namespace CsvMigrations\Controller;

use Cake\Utility\Hash;
use InvalidArgumentException;

/**
 * @deprecated v10.0.0 No longer used by internal code and not recommended.
 */
trait CsvMigrationUploadTrait
{

    /**
     * Upload field name
     *
     * @var string
     */
    protected $_upField = '';

    /**
     * Unlink the upload field from the given module record.
     * @todo Replace 'document' with dynamic field, Should be called only by ajax calls.
     *
     * @param int $id record id
     * @param string $field Field
     * @return void
     */
    public function unlinkUpload($id = null, $field = null)
    {
        if (is_null($id) || is_null($field)) {
            throw new InvalidArgumentException();
        }
        $entity = $this->{$this->name}->get($id);
        $entity = $this->{$this->name}->patchEntity($entity, [$field => null]);
        if ($this->{$this->name}->save($entity)) {
            $result['message'] = __d('CsvMigrations', 'Upload has been unlinked.');
        } else {
            $result['message'] = __d('CsvMigrations', 'Failed to unlink.');
        }
        $this->set('result', $result);
        $this->set('_serialize', 'result');
    }

    /**
     * Uploads the file and stores it to its related model.
     *
     * @param  object $relatedEnt Stored entity associated with file storage entity which is stored in this function.
     * @param  string $uploadField Name of the field.
     * @return void
     */
    protected function _upload($relatedEnt, $uploadField)
    {
        $this->_setUploadField($uploadField);
        //File Storage plugin store one upload file at a time.
        $data = $this->_UploadArrayPer($uploadField);
        if (!$this->_isInValidUpload($data)) {
            //Store the File Storage entity
            $fileStorEnt = $this->{$this->name}->uploaddocuments->newEntity($data);
            $user = $this->Auth->identify();
            $fileStorEnt = $this->{$this->name}->uploaddocuments->patchEntity(
                $fileStorEnt,
                [
                    'foreign_key' => $relatedEnt->get('id'), //We need the id of the stored record as foreign key
                    'user_id' => $user['id'],
                ]
            );
            if ($this->{$this->name}->uploaddocuments->save($fileStorEnt)) {
                $this->Flash->success(__('File uploaded.'));
                //Store to the upload field the ID of the File Storage entity
                //This is helpful for rendering the output.
                $relatedEnt = $this->{$this->name}->patchEntity(
                    $relatedEnt,
                    [$this->_upField => $fileStorEnt->get('id')]
                );
                if (!$this->{$this->name}->save($relatedEnt)) {
                    $this->Flash->error(__('Failed to update related to entity\'s field.'));
                }
                //Documents entities are also stored in files table.
                $this->_hasFiles($fileStorEnt, $relatedEnt, 'documentidfiles');
            } else {
                $this->Flash->error(__('Failed to upload.'));
            }
        }
    }

    /**
     * Only for the Documents module which has association with files.
     *
     * @param  object  $fileStorEnt FileStorage entity
     * @param  object  $documentEnt Document entity
     * @param  string $assoc        name of the association between Documents and Files
     * @return void
     */
    protected function _hasFiles($fileStorEnt, $documentEnt, $assoc)
    {
        $filesAssoc = $this->{$this->name}->association($assoc);
        if ($filesAssoc) {
            $fileEntity = $this->{$this->name}->$assoc->newEntity([
                        'document_id' => $documentEnt->get('id'),
                        'file_id' => $fileStorEnt->get('id')
                    ]);
            if (!$this->{$this->name}->$assoc->save($fileEntity)) {
                $this->Flash->error(__('Failed to update related entity.'));
            }
        }
    }

    /**
     * Check for upload in the post data.
     *
     * @param  array $data Data to be checked for invalid upload.
     * @return bool true for invalid upload and vice versa.
     */
    protected function _isInValidUpload($data = [])
    {
        return (bool)Hash::get($data, 'UploadDocuments.file.error');
    }

    /**
     * Setter of _upField variable
     *
     * @param string $field Name of the upload field.
     * @return void
     */
    protected function _setUploadField($field = null)
    {
        $this->_upField = $field;
    }

    /**
     * Getter of _upField variable
     *
     * @return string
     */
    protected function _getUploadField()
    {
        return $this->_upField;
    }

    /**
     * Extract from the request the given field and return it.
     *
     * @param  string $field name of the field to be extracted from the upload(s).
     * @return array|false
     */
    protected function _uploadArrayPer($field = '')
    {
        if (empty($field)) {
            $field = $this->_upField;
        }
        $data = $this->request->data;
        $uploadArray = Hash::get($data, 'UploadDocuments.file.' . $field);
        $data = Hash::remove($data, 'UploadDocuments.file' . $field);
        if (empty($uploadArray)) {
            return false;
        }
        $data = Hash::insert($data, 'UploadDocuments.file', $uploadArray);

        return $data;
    }
}
