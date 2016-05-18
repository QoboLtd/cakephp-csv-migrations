<?php
namespace CsvMigrations\Controller;

use App\Controller\AppController as BaseController;

class AppController extends BaseController
{
    /**
     * Called before the controller action. You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     * @link http://book.cakephp.org/3.0/en/controllers.html#request-life-cycle-callbacks
     */
    public function beforeFilter(\Cake\Event\Event $event)
    {
        parent::beforeFilter($event);

        /*
        pass module alias to the View
         */
        $this->set('moduleAlias', $this->{$this->name}->moduleAlias());
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set('entities', $this->paginate($this->{$this->name}));
        $this->render('CsvMigrations.Common/index');
        $this->set('_serialize', ['entities']);
    }

    /**
     * View method
     *
     * @param string|null $id Entity id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $entity = $this->{$this->name}->get($id, [
            'contain' => []
        ]);
        $this->set('entity', $entity);
        $this->render('CsvMigrations.Common/view');
        $this->set('_serialize', ['entity']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $entity = $this->{$this->name}->newEntity();
        if ($this->request->is('post')) {
            $entity = $this->{$this->name}->patchEntity($entity, $this->request->data);
            if ($this->{$this->name}->save($entity)) {
                if ($this->_hasUpload() && !$this->_isInValidUpload()) {
                    $this->_upload($entity);
                }
                $this->Flash->success(__('The record has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The record could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('entity'));
        $this->render('CsvMigrations.Common/add');
        $this->set('_serialize', ['entity']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Entity id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $entity = $this->{$this->name}->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $entity = $this->{$this->name}->patchEntity($entity, $this->request->data);
            if ($this->{$this->name}->save($entity)) {
                $this->Flash->success(__('The record has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The record could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('entity'));
        $this->render('CsvMigrations.Common/edit');
        $this->set('_serialize', ['entity']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Entity id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $entity = $this->{$this->name}->get($id);
        if ($this->{$this->name}->delete($entity)) {
            $this->Flash->success(__('The record has been deleted.'));
        } else {
            $this->Flash->error(__('The record could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Uploads the file and stores it to its related model.
     *
     * @param  Entity $relatedEntity Related entity of the upload.
     * @return void
     */
    protected function _upload($relatedEntity)
    {
        $this->request->allowMethod('post');
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
        $this->request->allowMethod('post');
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
        $this->request->allowMethod('post');
        return (bool)$this->request->data['UploadDocuments']['file']['error'];

    }
}
