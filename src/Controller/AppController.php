<?php
namespace CsvMigrations\Controller;

use App\Controller\AppController as BaseController;
use Cake\ORM\TableRegistry;
use CsvMigrations\FileUploadsUtils;

class AppController extends BaseController
{
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
     * @return void
     * @link http://book.cakephp.org/3.0/en/controllers.html#request-life-cycle-callbacks
     */
    public function beforeFilter(\Cake\Event\Event $event)
    {
        parent::beforeFilter($event);

        /*
        pass module alias to the View
         */
        $table = $this->loadModel();
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
        $entity = $this->{$this->name}->newEntity();
        if ($this->request->is('post')) {
            if ($this->request->data('btn_operation') == 'cancel') {
                return $this->redirect(['action' => 'index']);
            }

            $entity = $this->{$this->name}->patchEntity($entity, $this->request->data);
            if ($this->{$this->name}->save($entity)) {
                // handle file uploads if found in the request data
                if (isset($this->request->data[$this->name]['file'])) {
                    $this->_fileUploadsUtils->save($entity, $this->request->data[$this->name]['file']);
                }
                $this->Flash->success(__('The record has been saved.'));

                return $this->redirect(['action' => 'view', $entity->{$this->{$this->name}->primaryKey()}]);
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
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     * @param string|null $id Entity id.
     * @return mixed Redirects on successful edit, renders view otherwise.
     */
    public function edit($id = null)
    {
        $entity = $this->{$this->name}->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($this->request->data('btn_operation') == 'cancel') {
                return $this->redirect(['action' => 'view', $id]);
            }
            /*
            enable accessibility to associated entity's primary key to avoid associated entity getting flagged as new
             */
            $patchOptions = $this->{$this->name}->enablePrimaryKeyAccess();
            $entity = $this->{$this->name}->patchEntity($entity, $this->request->data, $patchOptions);
            if ($this->{$this->name}->save($entity)) {
                // handle file uploads if found in the request data
                if (isset($this->request->data[$this->name]['file'])) {
                    $this->_fileUploadsUtils->save($entity, $this->request->data[$this->name]['file']);
                }
                $this->Flash->success(__('The record has been saved.'));

                return $this->redirect(['action' => 'view', $id]);
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
            $conditions = [];
            $associationKey = null;

            foreach ($this->{$this->name}->associations() as $association) {
                if (!in_array($association->type(), ['manyToMany'])) {
                    $relation = $this->{$this->name}->{$association->name()};
                    $associationKey = $association->foreignKey();

                    $query = $relation->find('all')
                        ->where([ $association->foreignKey() => $id]);

                    if ($query->count() > 0) {
                        $query->update()
                                ->set([$associationKey => null])
                                ->where([$associationKey => $id]);
                        $query->execute();
                    }
                } else {
                    $assocName = $association->name();
                    $associationKey = $association->primaryKey();
                    $relation = $this->{$this->name};

                    $query = $relation->find('all', [
                        'conditions' => [$association->primaryKey() => $id],
                        'contain' => [
                            $assocName
                        ]
                    ]);
                    if ($query->count() > 0) {
                        $query->update()
                                ->set([$associationKey => null])
                                ->where([$associationKey => $id]);
                        $query->execute();
                    }
                }
            }

            $this->Flash->success(__('The record has been deleted.'));
        } else {
            $this->Flash->error(__('The record could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
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
        $entity = $this->{$this->name}->get($id);
        $assocEntity = $this->{$this->name}->{$assocName}->get($assocId);
        /*
        unlink associated record
         */
        $this->{$this->name}->{$assocName}->unlink($entity, [$assocEntity]);

        $this->Flash->success(__('The record has been unlinked.'));

        return $this->redirect($this->referer());
    }
}
