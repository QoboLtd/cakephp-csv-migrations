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

/**
 * DblistItems Controller
 *
 * @property \CsvMigrations\Model\Table\DblistItemsTable $DblistItems
 */
class DblistItemsController extends BaseController
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
        $tree = $this->DblistItems->find('treeEntities', ['listId' => $listId]);
        if ($tree->isEmpty()) {
            $this->Flash->set(__d('CsvMigrations', 'List is empty, do you want to add new item?'));

            return $this->redirect(['action' => 'add', $listId]);
        }

        $this->set(compact('tree', 'list'));
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
                $this->Flash->success(__d('CsvMigrations', 'The Database list item has been saved.'));

                return $this->redirect(['action' => 'index', $listId]);
            } else {
                $this->Flash->error(__d('CsvMigrations', 'The Database list item could not be saved. Please, try again.'));
            }
        }
        $list = $this->DblistItems->Dblists->get($listId);
        $tree = $this->DblistItems
            ->find('treeList', ['spacer' => '&nbsp;&nbsp;&nbsp;&nbsp;'])
            ->where(['dblist_id' => $listId]);
        $this->set(compact('dblistItem', 'tree', 'list'));
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
                $this->Flash->success(__('The Database list item has been saved.'));

                return $this->redirect(['action' => 'index', $dblistItem->get('dblist_id')]);
            } else {
                $this->Flash->error(__('The Database list item could not be saved. Please, try again.'));
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
            $this->Flash->success(__('The Database list item has been deleted.'));
        } else {
            $this->Flash->error(__('The Database list item could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }

    /**
     * Move the node.
     *
     * @param  string $id listitem id
     * @param  string $action move action
     * @throws InvalidPrimaryKeyException When provided id is invalid.
     * @return \Cake\Network\Response|null
     */
    public function moveNode($id = null, $action = '')
    {
        $this->request->allowMethod('post');
        $moveActions = ['up', 'down'];
        if (!in_array($action, $moveActions)) {
            $this->Flash->error(__d('CsvMigrations', 'Unknown move action.'));

            return $this->redirect($this->referer());
        }
        $node = $this->DblistItems->get($id);
        $moveFunction = 'move' . $action;
        if ($this->DblistItems->{$moveFunction}($node)) {
            $this->Flash->success(__d('CsvMigrations', '{0} has been moved {1} successfully.', $node->name, $action));
        } else {
            $this->Flash->error(__d('CsvMigrations', 'Fail to move {0} {1}.', $node->name, $action));
        }

        return $this->redirect($this->referer());
    }
}
