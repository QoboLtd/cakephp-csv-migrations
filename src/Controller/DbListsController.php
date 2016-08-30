<?php
namespace CsvMigrations\Controller;

use CsvMigrations\Controller\AppController;

/**
 * DbLists Controller
 *
 * @property \CsvMigrations\Model\Table\DbListsTable $DbLists
 */
class DbListsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $dbLists = $this->paginate($this->DbLists);

        $this->set(compact('dbLists'));
        $this->set('_serialize', ['dbLists']);
    }

    /**
     * View method
     *
     * @param string|null $id Db List id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $dbList = $this->DbLists->get($id, [
            'contain' => ['DbListItems']
        ]);

        $this->set('dbList', $dbList);
        $this->set('_serialize', ['dbList']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $dbList = $this->DbLists->newEntity();
        if ($this->request->is('post')) {
            $dbList = $this->DbLists->patchEntity($dbList, $this->request->data);
            if ($this->DbLists->save($dbList)) {
                $this->Flash->success(__('The db list has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The db list could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('dbList'));
        $this->set('_serialize', ['dbList']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Db List id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $dbList = $this->DbLists->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $dbList = $this->DbLists->patchEntity($dbList, $this->request->data);
            if ($this->DbLists->save($dbList)) {
                $this->Flash->success(__('The db list has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The db list could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('dbList'));
        $this->set('_serialize', ['dbList']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Db List id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $dbList = $this->DbLists->get($id);
        if ($this->DbLists->delete($dbList)) {
            $this->Flash->success(__('The db list has been deleted.'));
        } else {
            $this->Flash->error(__('The db list could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
