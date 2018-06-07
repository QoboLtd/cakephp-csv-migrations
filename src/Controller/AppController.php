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

use App\Controller\AppController as BaseController;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Log\Log;
use Cake\Utility\Inflector;
use CsvMigrations\Controller\Traits\ImportTrait;
use CsvMigrations\Event\EventName;
use CsvMigrations\Utility\Field;
use CsvMigrations\Utility\FileUpload;
use Exception;
use Psr\Http\Message\ResponseInterface;

class AppController extends BaseController
{
    use ImportTrait;

    protected $fileUpload;

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();

        $this->fileUpload = new FileUpload($this->{$this->name});

        $this->loadComponent('CsvMigrations.CsvView');
    }

    /**
     * Called before the controller action. You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * @param \Cake\Event\Event $event An Event instance
     * @return \Psr\Http\Message\ResponseInterface|void
     * @link http://book.cakephp.org/3.0/en/controllers.html#request-life-cycle-callbacks
     */
    public function beforeFilter(Event $event)
    {
        $result = parent::beforeFilter($event);
        if ($result instanceof ResponseInterface) {
            return $result;
        }

        if ($this->Auth->user() && method_exists($this->{$this->name}, 'setCurrentUser')) {
            $this->{$this->name}->setCurrentUser($this->Auth->user());
        }
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
        $entity = $this->{$this->name}->find()
            ->where([$this->{$this->name}->getPrimaryKey() => $id])
            ->first();

        if (empty($entity)) {
            $entity = $this->{$this->name}->find()
                ->applyOptions(['lookup' => true, 'value' => $id])
                ->firstOrFail();
        }

        $this->set('entity', $entity);
        $this->render('CsvMigrations.Common/view');
        $this->set('_serialize', ['entity']);
    }

    /**
     * Add method
     *
     * @return mixed Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $entity = $this->{$this->name}->newEntity();

        if (!empty($this->request->params['data'])) {
            $this->request->data = $this->request->params['data'];
        }

        if ($this->request->is('post')) {
            $response = $this->persistEntity($entity);
            if ($response) {
                return $response;
            }
        }

        $this->set('entity', $entity);
        $this->render('CsvMigrations.Common/add');
        $this->set('_serialize', ['entity']);
    }

    /**
     * Edit method
     *
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     * @param string|null $id Entity id.
     * @return mixed Redirects on successful edit, renders view otherwise.
     */
    public function edit($id = null)
    {
        $entity = $this->{$this->name}->find()
            ->where([$this->{$this->name}->getPrimaryKey() => $id])
            ->first();

        if (empty($entity)) {
            $entity = $this->{$this->name}->find()
                ->applyOptions(['lookup' => true, 'value' => $id])
                ->firstOrFail();
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            // enable accessibility to associated entity's primary key to avoid associated entity getting flagged as new
            $response = $this->persistEntity($entity, $this->{$this->name}->enablePrimaryKeyAccess());
            if ($response) {
                return $response;
            }
        }

        $this->set('entity', $entity);
        $this->render('CsvMigrations.Common/edit');
        $this->set('_serialize', ['entity']);
    }

    /**
     * Persist new/modified entity.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param array $options Patch options
     * @return \Cake\Http\Response|null
     */
    private function persistEntity(EntityInterface $entity, array $options = [])
    {
        $entity = $this->{$this->name}->patchEntity($entity, $this->request->data, $options);

        $saved = false;
        try {
            $saved = $this->{$this->name}->save($entity, ['lookup' => true]);
        } catch (Exception $e) {
            Log::warning($e->getMessage());
        }

        if ($entity->getErrors()) {
            Log::warning($entity->getErrors());
        }

        if (! $saved) {
            $this->Flash->error(__('The record could not be saved, please try again.'));
        }

        if ($saved) {
            $this->Flash->success(__('The record has been saved.'));
            // handle file uploads if found in the request data
            $this->fileUpload->linkFilesToEntity($entity, $this->{$this->name}, $this->request->data);

            $url = $this->{$this->name}->getParentRedirectUrl($this->{$this->name}, $entity);
            $url = ! empty($url) ? $url : ['action' => 'view', $entity->get($this->{$this->name}->getPrimaryKey())];

            return $this->redirect($url);
        }

        return null;
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
        $model = $this->{$this->name};
        $entity = $model->get($id);

        if ($model->delete($entity)) {
            $this->Flash->success(__('The record has been deleted.'));
        } else {
            $this->Flash->error(__('The record could not be deleted. Please, try again.'));
        }

        $url = $this->referer();

        if (false !== strpos($url, $id)) {
            $url = ['action' => 'index'];
        }

        return $this->redirect($url);
    }

    /**
     * Unlink method
     *
     * @param string $id Entity id.
     * @param string $assocName Association Name.
     * @param string $assocId Associated Entity id.
     * @return \Cake\Network\Response|null Redirects to referer.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function unlink($id, $assocName, $assocId)
    {
        $this->request->allowMethod(['post']);
        $model = $this->{$this->name};
        $entity = $model->get($id);
        $assocEntity = $model->{$assocName}->get($assocId);

        // unlink associated record
        $model->{$assocName}->unlink($entity, [$assocEntity]);

        $this->Flash->success(__('The record has been unlinked.'));

        return $this->redirect($this->referer());
    }

    /**
     * Link Method
     *
     * Embedded linking form for many-to-many records,
     * link the associations without calling direct edit() action
     * on the origin entity - it prevents overwritting the associations
     *
     * @param string $id Entity id.
     * @param string $associationName Association Name.
     * @return \Cake\Network\Response|null Redirects to referer.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function link($id, $associationName)
    {
        $this->request->allowMethod(['post']);

        $association = $this->{$this->name}->{$associationName};
        $ids = $this->request->getData($associationName . '._ids');

        if (empty($ids)) {
            $this->Flash->error(__('No records provided for linking.'));

            return $this->redirect($this->referer());
        }

        $query = $association->find('all')
            ->where([$association->getPrimaryKey() . ' IN' => $ids]);

        if ($query->isEmpty()) {
            $this->Flash->error(__('No records found for linking.'));

            return $this->redirect($this->referer());
        }

        if (! $association->link($this->{$this->name}->get($id), $query->toArray())) {
            $this->Flash->error(__('Failed to link records.'));

            return $this->redirect($this->referer());
        }

        $this->Flash->success(sprintf('(%s)', count($ids)) . ' ' . __('records have been linked.'));

        return $this->redirect($this->referer());
    }

    /**
     * Batch operations action.
     *
     * @param string $operation Batch operation.
     * @return \Cake\Network\Response|void Redirects to referer.
     */
    public function batch($operation)
    {
        $this->request->allowMethod(['post']);

        $redirectUrl = $this->getBatchRedirectUrl();

        $batchIds = (array)$this->request->data('batch.ids');
        if (empty($batchIds)) {
            $this->Flash->error(__('No records selected.'));

            return $this->redirect($redirectUrl);
        }

        $batchIdsCount = count($batchIds);

        // broadcast batch ids event
        $event = new Event((string)EventName::BATCH_IDS(), $this, [
            $batchIds,
            $operation,
            $this->Auth->user()
        ]);
        $this->eventManager()->dispatch($event);

        $batchIds = is_array($event->result) ? $event->result : $batchIds;

        if (empty($batchIds)) {
            $operation = strtolower(Inflector::humanize($operation));
            $this->Flash->error(__('Insufficient permissions to ' . $operation . ' the selected records.'));

            return $this->redirect($redirectUrl);
        }

        if ('delete' === $operation) {
            $conditions = [$this->{$this->name}->getPrimaryKey() . ' IN' => $batchIds];
            // execute batch delete
            if ($this->{$this->name}->deleteAll($conditions)) {
                $this->Flash->success(
                    __(count($batchIds) . ' of ' . $batchIdsCount . ' selected records have been deleted.')
                );
            } else {
                $this->Flash->error(__('Selected records could not be deleted. Please, try again.'));
            }

            return $this->redirect($redirectUrl);
        }

        if ('edit' === $operation && (bool)$this->request->data('batch.execute')) {
            $fields = (array)$this->request->data($this->name);
            if (empty($fields)) {
                $this->Flash->error(__('Selected records could not be updated. No changes provided.'));

                return $this->redirect($redirectUrl);
            }

            $conditions = [$this->{$this->name}->getPrimaryKey() . ' IN' => $batchIds];
            // execute batch edit
            if ($this->{$this->name}->updateAll($fields, $conditions)) {
                $this->Flash->success(
                    __(count($batchIds) . ' of ' . $batchIdsCount . ' selected records have been updated.')
                );
            } else {
                $this->Flash->error(__('Selected records could not be updated. Please, try again.'));
            }

            return $this->redirect($redirectUrl);
        }

        $this->set('entity', $this->{$this->name}->newEntity());
        $this->set('fields', Field::getCsvView($this->{$this->name}, $operation, true, true));
        $this->render('CsvMigrations.Common/batch');
    }

    /**
     * Fetch batch redirect url.
     *
     * @return string
     */
    protected function getBatchRedirectUrl()
    {
        // default url
        $result = ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'index'];

        $currentUrl = $this->request->env('HTTP_ORIGIN') . $this->request->getRequestTarget();
        // if referer does not match current url, redirect to referer (delete action)
        if (false === strpos($this->referer(), $currentUrl)) {
            $result = $this->referer();
        }

        // use batch redirect url if provided (edit action)
        if ($this->request->data('batch.redirect_url')) {
            $result = $this->request->data('batch.redirect_url');
        }

        return $result;
    }
}
