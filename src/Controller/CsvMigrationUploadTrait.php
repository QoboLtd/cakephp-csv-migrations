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
     * @param  Entity $relatedEntity Related entity of the upload.
     * @return void
     */
    protected function _upload($relatedEntity, $uploadField)
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
            $relatedEntity = $this->{$this->name}->patchEntity($relatedEntity, [$uploadField => $entity->get('id')]);
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
