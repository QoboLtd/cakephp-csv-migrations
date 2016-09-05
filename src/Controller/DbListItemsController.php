<?php
namespace CsvMigrations\Controller;

use CsvMigrations\Controller\AppController;

/**
 * DbListItems Controller
 *
 * @property \CsvMigrations\Model\Table\DbListItemsTable $DbListItems
 */
class DbListItemsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['DbLists']
        ];
        $dbListItems = $this->paginate($this->DbListItems);

        $this->set(compact('dbListItems'));
        $this->set('_serialize', ['dbListItems']);
    }

    /**
     * View method
     *
     * @param string|null $id Db List Item id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $dbListItem = $this->DbListItems->get($id, [
            'contain' => ['DbLists']
        ]);

        $this->set('dbListItem', $dbListItem);
        $this->set('_serialize', ['dbListItem']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $dbListItem = $this->DbListItems->newEntity();
        if ($this->request->is('post')) {
            $dbListItem = $this->DbListItems->patchEntity($dbListItem, $this->request->data);
            if ($this->DbListItems->save($dbListItem)) {
                $this->Flash->success(__('The db list item has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The db list item could not be saved. Please, try again.'));
            }
        }
        $dbLists = $this->DbListItems->DbLists->find('list', ['limit' => 200]);
        $this->set(compact('dbListItem', 'dbLists'));
        $this->set('_serialize', ['dbListItem']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Db List Item id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $dbListItem = $this->DbListItems->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $dbListItem = $this->DbListItems->patchEntity($dbListItem, $this->request->data);
            if ($this->DbListItems->save($dbListItem)) {
                $this->Flash->success(__('The db list item has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The db list item could not be saved. Please, try again.'));
            }
        }
        $dbLists = $this->DbListItems->DbLists->find('list', ['limit' => 200]);
        $this->set(compact('dbListItem', 'dbLists'));
        $this->set('_serialize', ['dbListItem']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Db List Item id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $dbListItem = $this->DbListItems->get($id);
        if ($this->DbListItems->delete($dbListItem)) {
            $this->Flash->success(__('The db list item has been deleted.'));
        } else {
            $this->Flash->error(__('The db list item could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
