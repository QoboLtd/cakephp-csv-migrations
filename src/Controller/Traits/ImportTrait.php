<?php
namespace CsvMigrations\Controller\Traits;

use Cake\ORM\TableRegistry;
use CsvMigrations\Utility\Import as ImportUtility;

trait ImportTrait
{
    /**
     * Import action.
     *
     * @param string|null $id Import id
     * @return \Cake\Network\Response|void
     */
    public function import($id = null)
    {
        $table = TableRegistry::get('CsvMigrations.Imports');

        $entity = is_null($id) ? $table->newEntity() : $table->get($id);

        // AJAX logic
        if ($this->request->accepts('application/json')) { // Import/progress.ctp
            $utility = new ImportUtility($this->request, $this->Flash);
            $columns = ['row_number', 'status', 'status_message'];
            $query = $utility->getImportResults($entity, $columns);

            $pagination = [
                'count' => $query->count()
            ];

            $this->set([
                'success' => true,
                'data' => $utility->toDatatables($this->paginate($query), $columns, $this->{$this->name}),
                'pagination' => $pagination,
                '_serialize' => ['success', 'data', 'pagination']
            ]);

            return;
        }

        // POST logic
        if ($this->request->is('post')) { // Import/upload.ctp
            $utility = new ImportUtility($this->request, $this->Flash);
            $filename = $utility->upload();
            if (!empty($filename) && $utility->create($table, $entity, $filename)) {
                return $this->redirect([$entity->id]);
            }
        }

        // PUT logic
        if ($this->request->is('put')) { // Import/mapping.ctp
            $utility = new ImportUtility($this->request, $this->Flash);
            if ($utility->mapColumns($table, $entity)) {
                $utility->setImportResults($entity);

                return $this->redirect([$entity->id]);
            } else {
                $this->Flash->error(__('Unable to set import options.'));
            }
        }

        // GET logic
        if (!$entity->isNew()) {
            if (!$entity->get('options')) { // Import/mapping.ctp
                $utility = new ImportUtility($this->request, $this->Flash);
                $this->set('headers', ImportUtility::getUploadHeaders($entity));
                $this->set('columns', $utility->getTableColumns());
            }
        } else { // Import/upload.ctp
            $query = $table->find('all')
                ->where(['model_name' => $this->{$this->name}->getRegistryAlias()])
                ->order(['created' => 'desc']);

            $this->set('existingImports', $query->all());
        }

        $this->set('import', $entity);

        if ($entity->isNew()) {
            $this->render('CsvMigrations.Import/upload');
        } else {
            if (!$entity->get('options')) {
                $this->render('CsvMigrations.Import/mapping');
            } else {
                $this->render('CsvMigrations.Import/progress');
            }
        }
    }
}
