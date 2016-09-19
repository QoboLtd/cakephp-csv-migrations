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
        $list = $this->DblistItems->Dblists->get($listId);
        $dblistItems = $this->DblistItems
            ->find('treeList', ['spacer' => '&nbsp;&nbsp;&nbsp;&nbsp;'])
            ->where(['dblist_id' => $listId]);
        if ($dblistItems->isEmpty()) {
            $this->Flash->set(__d('CsvMigrations', 'List is empty, do you want to add new item?'));

            return $this->redirect(['action' => 'add', $listId]);
        }
        $this->set(compact('dblistItems', 'list'));
    }

    /**
     * Add method
     *
     * @param string $listId List's id
     * @return \Cake\Network\Response|null
     */
    public function add($listId = null)
    {
        $dblistItem = $this->DblistItems->newEntity();
        if ($this->request->is('post')) {
            $dblistItem = $this->DblistItems->patchEntity($dblistItem, $this->request->data);
            if ($this->DblistItems->save($dblistItem)) {
                $this->Flash->success(__d('CsvMigrations', 'The dblist item has been saved.'));

                return $this->redirect(['action' => 'index', $listId]);
            } else {
                $this->Flash->error(__d('CsvMigrations', 'The dblist item could not be saved. Please, try again.'));
            }
        }
        $list = $this->DblistItems->Dblists->get($listId);
        $tree = $this->DblistItems
            ->find('treeList', ['spacer' => '&nbsp;&nbsp;&nbsp;&nbsp;'])
            ->where(['dblist_id' => $listId]);
        $this->set(compact('dblistItem', 'tree', 'dblistItems', 'list'));
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
        $dblistItem = $this->DblistItems->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $dblistItem = $this->DblistItems->patchEntity($dblistItem, $this->request->data);
            if ($this->DblistItems->save($dblistItem)) {
                $this->Flash->success(__('The dblist item has been saved.'));

                return $this->redirect(['action' => 'index', $dblistItem->get('dblist_id')]);
            } else {
                $this->Flash->error(__('The dblist item could not be saved. Please, try again.'));
            }
        }
        $list = $this->DblistItems->Dblists->get($dblistItem->get('dblist_id'));
        $tree = $this->DblistItems
            ->find('treeList', ['spacer' => '&nbsp;&nbsp;&nbsp;&nbsp;'])
            ->where(['dblist_id' => $dblistItem->get('dblist_id')]);
        $this->set(compact('dblistItem', 'list', 'tree'));
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

        return $this->redirect($this->referer());
    }
}
