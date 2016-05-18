<?php
namespace CsvMigrations\Controller;

trait CsvMigrationUploadTrait
{
    /**
     * Uploads the file and stores it to its related model.
     *
     * @param  Entity $relatedEntity Related entity of the upload.
     * @return void
     */
    protected function _upload($relatedEntity)
    {
        $user = $this->Auth->identify();
        $entity = $this->{$this->name}->uploaddocuments->newEntity($this->request->data);
        $entity = $this->{$this->name}->uploaddocuments->patchEntity(
            $entity,
            [
                'foreign_key' => $relatedEntity->get('id'),
                'user_id' => $user['id'],
            ]
        );
        if ($this->{$this->name}->uploaddocuments->save($entity)) {
            /**
             * Stores the id of the FileStorage entity to the document field.
             * The 'id' is used to get the entity on renderValue to generate the URL of the uploaded file.
             * @see CsvMigrations\FieldHandlers\FileFieldHandler renderValue()
             * @todo document should not be hardcoded.
             */
            $relatedEntity = $this->{$this->name}->patchEntity($relatedEntity, ['document' => $entity->get('id')]);
            if (!$this->{$this->name}->save($relatedEntity)) {
                $this->Flash->error(__('Failed to update related entity.'));
            }
            $this->Flash->success(__('File uploaded.'));
        } else {
            $this->Flash->error(__('Failed to upload.'));
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
}
