<?php
namespace CsvMigrations\Controller;

use CsvMigrations\Controller\AppController;

/**
 * Dblists Controller
 *
 * @property \CsvMigrations\Model\Table\DblistsTable $Dblists
 */
class DblistsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $dblists = $this->paginate($this->Dblists);

        $this->set(compact('dblists'));
        $this->set('_serialize', ['dblists']);
    }

    /**
     * View method
     *
     * @param string|null $id Dblist id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $dblist = $this->Dblists->get($id, [
            'contain' => ['DblistItems']
        ]);

        $this->set('dblist', $dblist);
        $this->set('_serialize', ['dblist']);
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
                $this->Flash->success(__('The dblist has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The dblist could not be saved. Please, try again.'));
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
                $this->Flash->success(__('The dblist has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The dblist could not be saved. Please, try again.'));
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
            $this->Flash->success(__('The dblist has been deleted.'));
        } else {
            $this->Flash->error(__('The dblist could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
