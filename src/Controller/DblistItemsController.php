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
use Cake\Http\Response;

/**
 * DblistItems Controller
 *
 * @property \CsvMigrations\Model\Table\DblistItemsTable $DblistItems
 * @property \CsvMigrations\Model\Table\DblistsTable $DblistItems->Dblists
 */
class DblistItemsController extends BaseController
{
    /**
     * Index method
     *
     * @param string $id Associated Dblist id
     * @return \Cake\Network\Response|null
     */
    public function index(string $id) : ?Response
    {
        $list = $this->DblistItems->Dblists->get($id);
        $query = $this->DblistItems->find('treeEntities', ['listId' => $id]);

        if ($query->isEmpty()) {
            $this->Flash->set('List is empty, do you want to add new item?');

            return $this->redirect(['action' => 'add', $id]);
        }

        $this->set(compact('query', 'list'));
        $this->set('_serialize', ['entities']);
    }

    /**
     * Add method
     *
     * @param string $id Associated Dblist id
     * @return \Cake\Http\Response|null
     */
    public function add(string $id) : ?Response
    {
        $entity = $this->DblistItems->newEntity();

        if ($this->request->is('post')) {
            $entity = $this->DblistItems->patchEntity($entity, array_merge((array)$this->request->getData(), ['dblist_id' => $id]));

            if ($this->DblistItems->save($entity)) {
                $this->Flash->success('The database list item has been saved.');

                return $this->redirect(['action' => 'index', $id]);
            }

            $this->Flash->error('The database list item could not be saved. Please, try again.');
        }

        $tree = $this->DblistItems->find('treeList', ['spacer' => '&nbsp;&nbsp;&nbsp;&nbsp;'])
            ->where(['dblist_id' => $id]);

        $this->set(compact('entity', 'tree'));
        $this->set('_serialize', ['entity']);
    }

    /**
     * Edit method
     *
     * @param string $id Dblist Item id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(string $id) : ?Response
    {
        $entity = $this->DblistItems->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $entity = $this->DblistItems->patchEntity($entity, (array)$this->request->getData());

            if ($this->DblistItems->save($entity)) {
                $this->Flash->success('The database list item has been saved.');

                return $this->redirect(['action' => 'index', $entity->get('dblist_id')]);
            }

            $this->Flash->error('The database list item could not be saved. Please, try again.');
        }

        $tree = $this->DblistItems->find('treeList', ['spacer' => '&nbsp;&nbsp;&nbsp;&nbsp;'])
            ->where(['dblist_id' => $entity->get('dblist_id')]);

        $this->set(compact('entity', 'tree'));
        $this->set('_serialize', ['entity']);
    }

    /**
     * Delete method
     *
     * @param string $id Dblist Item id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(string $id) : ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $entity = $this->DblistItems->get($id);

        if ($this->DblistItems->delete($entity)) {
            $this->Flash->success('The database list item has been deleted.');
        } else {
            $this->Flash->error('The database list item could not be deleted. Please, try again.');
        }

        return $this->redirect($this->referer());
    }

    /**
     * Move the node.
     *
     * @param string $id listitem id
     * @param string $action move action
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @return \Cake\Http\Response|null
     */
    public function moveNode(string $id, string $action = '') : ?Response
    {
        $this->request->allowMethod('post');

        if (!in_array($action, ['up', 'down'])) {
            $this->Flash->error((string)__('Unknown move action "{0}".', $action));

            return $this->redirect($this->referer());
        }

        $entity = $this->DblistItems->get($id);

        if ($this->DblistItems->{'move' . $action}($entity)) {
            $this->Flash->success((string)__('{0} has been moved {1} successfully.', $entity->get('name'), $action));
        } else {
            $this->Flash->error((string)__('Fail to move {0} {1}.', $entity->get('name'), $action));
        }

        return $this->redirect($this->referer());
    }
}
