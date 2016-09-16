<?php
namespace CsvMigrations\Controller;

use CsvMigrations\Controller\AppController;

/**
 * DblistItems Controller
 *
 * @property \CsvMigrations\Model\Table\DblistItemsTable $DblistItems
 */
class DblistItemsController extends AppController
{

    /**
     * Index method
     *
     * @param string $listId List's id
     * @return \Cake\Network\Response|null
     */
    public function index($listId = null)
    {
        $dblistItems = [];
        if ($this->DblistItems->Dblists->exists(['id' => $listId])) {
            $dblistItems = $this->DblistItems
                ->find('treeList', ['spacer' => '&nbsp;&nbsp;&nbsp;&nbsp;'])
                ->where(['dblist_id' => $listId]);
            if ($dblistItems->isEmpty()) {
                $this->Flash->set(__d('CsvMigrations', 'List is empty, do you want to add new item?'));

                return $this->redirect(['controller' => 'DblistItems', 'action' => 'add', $listId]);
            }
        } else {
            $this->Flash->set(__d('CsvMigrations', 'Error, the list cannot be found.'));

            return $this->redirect(['controller' => 'Dblists', 'action' => 'index']);
        }
        $this->set(compact('dblistItems'));
    }

    /**
     * View method
     *
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @param string|null $id Dblist Item id.
     * @return void
     */
    public function view($id = null)
    {
        $dblistItem = $this->DblistItems->get($id, [
            'contain' => ['Dblists']
        ]);

        $this->set('dblistItem', $dblistItem);
        $this->set('_serialize', ['dblistItem']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add($listId = null)
    {
        $dblistItem = $this->DblistItems->newEntity();
        if ($this->request->is('post')) {
            $dblistItem = $this->DblistItems->patchEntity($dblistItem, $this->request->data);
            if ($this->DblistItems->save($dblistItem)) {
                $this->Flash->success(__('The dblist item has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The dblist item could not be saved. Please, try again.'));
            }
        }
        $dblists = $this->DblistItems->Dblists->find('list', ['limit' => 200]);
        $tree = $this->DblistItems->find('treeList');
        $this->set(compact('dblistItem', 'dblists', 'tree'));
        $this->set('_serialize', ['dblistItem']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Dblist Item id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $dblistItem = $this->DblistItems->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $dblistItem = $this->DblistItems->patchEntity($dblistItem, $this->request->data);
            if ($this->DblistItems->save($dblistItem)) {
                $this->Flash->success(__('The dblist item has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The dblist item could not be saved. Please, try again.'));
            }
        }
        $dblists = $this->DblistItems->Dblists->find('list', ['limit' => 200]);
        $tree = $this->DblistItems->find('treeList');
        $this->set(compact('dblistItem', 'dblists', 'tree'));
        $this->set('_serialize', ['dblistItem']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Dblist Item id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $dblistItem = $this->DblistItems->get($id);
        if ($this->DblistItems->delete($dblistItem)) {
            $this->Flash->success(__('The dblist item has been deleted.'));
        } else {
            $this->Flash->error(__('The dblist item could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
