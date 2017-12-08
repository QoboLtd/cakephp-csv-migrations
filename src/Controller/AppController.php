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
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\Controller\Traits\ImportTrait;
use CsvMigrations\Event\EventName;
use CsvMigrations\FileUploadsUtils;
use CsvMigrations\Utility\Field;

class AppController extends BaseController
{
    use ImportTrait;

    protected $_fileUploadsUtils;

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();

        $this->_fileUploadsUtils = new FileUploadsUtils($this->{$this->name});

        $this->loadComponent('CsvMigrations.CsvView');
    }

    /**
     * Called before the controller action. You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * @param \Cake\Event\Event $event An Event instance
     * @return void|\Cake\Http\Response
     * @link http://book.cakephp.org/3.0/en/controllers.html#request-life-cycle-callbacks
     */
    public function beforeFilter(\Cake\Event\Event $event)
    {
        $result = parent::beforeFilter($event);
        if ($result instanceof Response) {
            return $result;
        }

        // pass module alias to the View
        $table = $this->loadModel();

        if ($this->Auth->user()) {
            if (method_exists($table, 'setCurrentUser')) {
                $table->setCurrentUser($this->Auth->user());
            }
        }

        if (method_exists($table, 'moduleAlias')) {
            $alias = $table->moduleAlias();
        } else {
            $alias = $table->alias();
        }
        $this->set('moduleAlias', $alias);
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->render('CsvMigrations.Common/index');
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
     * @return mixed Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $model = $this->{$this->name};
        $entity = $model->newEntity();

        if (!empty($this->request->params['data'])) {
            $this->request->data = $this->request->params['data'];
        }

        if ($this->request->is('post')) {
            if ($this->request->data('btn_operation') == 'cancel') {
                return $this->redirect(['action' => 'index']);
            }

            $entity = $model->patchEntity($entity, $this->request->data);

            $saved = null;
            $reason = 'Please try again later.';
            // TODO: Log the error.
            try {
                $saved = $model->save($entity);
            } catch (\PDOException $e) {
                if (!empty($e->errorInfo[2])) {
                    $reason = $e->errorInfo[2];
                }
            } catch (\Exception $e) {
            }

            if ($saved) {
                $linked = $this->_fileUploadsUtils->linkFilesToEntity($entity, $model, $this->request->data);

                $this->Flash->success(__('The record has been saved.'));

                $redirectUrl = $model->getParentRedirectUrl($model, $entity);
                if (empty($redirectUrl)) {
                    return $this->redirect(['action' => 'view', $entity->{$model->primaryKey()}]);
                } else {
                    return $this->redirect($redirectUrl);
                }
            } else {
                $this->Flash->error(__('The record could not be saved. ' . $reason));
            }
        }

        $this->set(compact('entity'));
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
        $model = $this->{$this->name};
        $entity = $model->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($this->request->data('btn_operation') == 'cancel') {
                return $this->redirect(['action' => 'view', $id]);
            }

            // enable accessibility to associated entity's primary key to avoid associated entity getting flagged as new
            $patchOptions = $model->enablePrimaryKeyAccess();
            $entity = $model->patchEntity($entity, $this->request->data, $patchOptions);

            $saved = null;
            $reason = 'Please try again later.';
            // TODO: Log the error.
            try {
                $saved = $model->save($entity);
            } catch (\PDOException $e) {
                if (!empty($e->errorInfo[2])) {
                    $reason = $e->errorInfo[2];
                }
            } catch (\Exception $e) {
            }

            if ($saved) {
                // handle file uploads if found in the request data
                $linked = $this->_fileUploadsUtils->linkFilesToEntity($entity, $model, $this->request->data);

                $this->Flash->success(__('The record has been saved.'));

                $redirectUrl = $model->getParentRedirectUrl($model, $entity);
                if (empty($redirectUrl)) {
                    return $this->redirect(['action' => 'view', $entity->{$model->primaryKey()}]);
                } else {
                    return $this->redirect($redirectUrl);
                }
            } else {
                $this->Flash->error(__('The record could not be saved. ' . $reason));
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
     * @return \Cake\Network\Response|null Redirects to referer.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function link()
    {
        $this->request->allowMethod(['post']);

        $data = $this->request->getData();
        $assocEntities = [];
        $assocName = $data['assocName'];

        $model = $this->{$this->name};
        $entity = $model->get($data['id']);

        if (!empty($data[$assocName]) && isset($data[$assocName]['_ids'])) {
            foreach ($data[$assocName]['_ids'] as $assocId) {
                $assocEntity = $model->{$assocName}->get($assocId);
                if (!empty($assocEntity)) {
                    array_push($assocEntities, $assocEntity);
                }
            }
        }

        if (!empty($assocEntities)) {
            $model->{$assocName}->link($entity, $assocEntities);
            $this->Flash->success(__('The record has been linked.'));
        }

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
