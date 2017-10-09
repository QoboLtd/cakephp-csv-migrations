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
 * Dblists Controller
 *
 * @property \CsvMigrations\Model\Table\DblistsTable $Dblists
 */
class DblistsController extends BaseController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $dblists = $this->Dblists->find('all');

        $this->set(compact('dblists'));
        $this->set('_serialize', ['dblists']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $dblist = $this->Dblists->newEntity();
        if ($this->request->is('post')) {
            $dblist = $this->Dblists->patchEntity($dblist, $this->request->data);
            if ($this->Dblists->save($dblist)) {
                $this->Flash->success(__('The Database list has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The Database list could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('dblist'));
        $this->set('_serialize', ['dblist']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Dblist id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $dblist = $this->Dblists->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $dblist = $this->Dblists->patchEntity($dblist, $this->request->data);
            if ($this->Dblists->save($dblist)) {
                $this->Flash->success(__('The Database list has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The Database list could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('dblist'));
        $this->set('_serialize', ['dblist']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Dblist id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $dblist = $this->Dblists->get($id);
        if ($this->Dblists->delete($dblist)) {
            $this->Flash->success(__('The Database list has been deleted.'));
        } else {
            $this->Flash->error(__('The Database list could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
