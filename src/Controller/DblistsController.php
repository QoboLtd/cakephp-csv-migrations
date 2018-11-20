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
 * Dblists Controller
 *
 * @property \CsvMigrations\Model\Table\DblistsTable $Dblists
 */
class DblistsController extends BaseController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void|null
     */
    public function index()
    {
        $entities = $this->Dblists->find('all');

        $this->set(compact('entities'));
        $this->set('_serialize', ['entities']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|void|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $entity = $this->Dblists->newEntity();
        if ($this->request->is('post')) {
            $entity = $this->Dblists->patchEntity($entity, (array)$this->request->getData());
            if ($this->Dblists->save($entity)) {
                $this->Flash->success('The database list has been saved.');

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error('The database list could not be saved. Please, try again.');
        }

        $this->set(compact('entity'));
        $this->set('_serialize', ['entity']);
    }

    /**
     * Edit method
     *
     * @param string $id Dblist id.
     * @return \Cake\Http\Response|void|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(string $id)
    {
        $entity = $this->Dblists->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $entity = $this->Dblists->patchEntity($entity, (array)$this->request->getData());
            if ($this->Dblists->save($entity)) {
                $this->Flash->success('The database list has been saved.');

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error('The database list could not be saved. Please, try again.');
        }

        $this->set(compact('entity'));
        $this->set('_serialize', ['entity']);
    }

    /**
     * Delete method
     *
     * @param string $id Dblist id.
     * @return \Cake\Http\Response|void|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(string $id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $dblist = $this->Dblists->get($id);
        if ($this->Dblists->delete($dblist)) {
            $this->Flash->success('The database list has been deleted.');
        } else {
            $this->Flash->error('The database list could not be deleted. Please, try again.');
        }

        return $this->redirect(['action' => 'index']);
    }
}
