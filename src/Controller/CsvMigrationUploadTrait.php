<?php
namespace CsvMigrations\Controller;

trait CsvMigrationUploadTrait
{
    /**
     * Unlink the upload field from the given module record.
     * @todo Replace 'document' with dynamic field, Should be called only by ajax calls.
     *
     * @param  int $id record id
     */
    public function unlinkUpload($id = null)
    {
        $entity = $this->{$this->name}->get($id);
        $entity = $this->{$this->name}->patchEntity($entity, ['document' => null]);
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
     * @param  Entity $relatedEnt Related entity of the upload.
     * @return void
     */
    protected function _upload($relatedEnt, $uploadField)
    {
        $user = $this->Auth->identify();
        //Store the File Storage entity
        $fileStorEnt = $this->{$this->name}->uploaddocuments->newEntity($this->request->data);
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
                [$uploadField => $fileStorEnt->get('id')]
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

    protected function _hasFiles($fileStorEnt, $relatedEnt, $assoc)
    {
        $filesAssoc = $this->{$this->name}->association($assoc);
        if ($filesAssoc) {
            $fileEntity = $this->{$this->name}->$assoc->newEntity([
                        'document_id' => $relatedEnt->get('id'),
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
     * @return boolean true if there is an upload array as defined by PHP.
     */
    protected function _hasUpload()
    {
        if (!isset($this->request->data['UploadDocuments'])) {
            return false;
        }

        if (!is_array($this->request->data['UploadDocuments']['file'])) {
            return false;
        }

        return true;
    }

    /**
     * Check for upload in the post data.
     *
     * @return boolean true for invalid upload and vice versa.
     */
    protected function _isInValidUpload()
    {
        return (bool)$this->request->data['UploadDocuments']['file']['error'];
    }

    /**
     * Find the field which are typed file
     *
     * @return array
     */
    protected function _getCsvUploadFields()
    {
        $result = [];
        $csvFields = $this->{$this->name}->getFieldsDefinitions();
        foreach ($csvFields as $field) {
            if ($field['type'] === 'file') {
                $result[] = $field['name'];
            }
        }

        return $result;
    }
}
