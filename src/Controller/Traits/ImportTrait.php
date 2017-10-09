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
namespace CsvMigrations\Controller\Traits;

use Cake\ORM\TableRegistry;
use CsvMigrations\Utility\Import as ImportUtility;

trait ImportTrait
{
    /**
     * Import action.
     *
     * @param string|null $id Import id
     * @return \Cake\Http\Response|void
     */
    public function import($id = null)
    {
        $table = TableRegistry::get('CsvMigrations.Imports');

        $entity = is_null($id) ? $table->newEntity() : $table->get($id);

        // AJAX logic
        if ($this->request->accepts('application/json')) { // Import/progress.ctp
            $this->viewBuilder()->className('Json');
            $utility = new ImportUtility($this->{$this->name}, $this->request, $this->Flash);
            $columns = ['row_number', 'status', 'status_message'];
            $query = $utility->getImportResults($entity, $columns);

            $pagination = [
                'count' => $query->count()
            ];

            $data = ImportUtility::toDatatables($this->paginate($query), $columns);
            $data = ImportUtility::actionButtons($this->paginate($query), $this->{$this->name}, $data);

            if (in_array('status', $columns)) {
                $data = ImportUtility::setStatusLabels($data, array_search('status', $columns));
            }

            $this->set([
                'success' => true,
                'data' => $data,
                'pagination' => $pagination,
                '_serialize' => ['success', 'data', 'pagination']
            ]);

            return;
        }

        // POST logic
        if ($this->request->is('post')) { // Import/upload.ctp
            $utility = new ImportUtility($this->{$this->name}, $this->request, $this->Flash);
            $filename = $utility->upload();
            if (!empty($filename) && $utility->create($table, $entity, $filename)) {
                return $this->redirect([$entity->id]);
            }
        }

        // PUT logic
        if ($this->request->is('put')) { // Import/mapping.ctp
            $options = ImportUtility::prepareOptions($this->request->data('options'));
            $entity = $table->patchEntity($entity, ['options' => $options]);
            if ($table->save($entity)) {
                return $this->redirect($this->request->here);
            } else {
                $this->Flash->error(__('Unable to set import options.'));
            }
        }

        // GET logic
        if (!$entity->isNew()) {
            $utility = new ImportUtility($this->{$this->name}, $this->request, $this->Flash);
            if (!$entity->get('options')) { // Import/mapping.ctp
                $this->set('headers', ImportUtility::getUploadHeaders($entity));
                $this->set('columns', $utility->getTableColumns());
            } else { // Import/progress.ctp
                $resultsTable = TableRegistry::get('CsvMigrations.ImportResults');
                $this->set('importCount', $resultsTable->find('imported', ['import' => $entity])->count());
                $this->set('pendingCount', $resultsTable->find('pending', ['import' => $entity])->count());
                $this->set('failCount', $resultsTable->find('failed', ['import' => $entity])->count());
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

    /**
     * Import download action.
     *
     * @param string $id Import id
     * @param string $type Export Type (supported values: 'original', 'processed')
     * @return \Cake\Http\Response
     */
    public function importDownload($id, $type = 'original')
    {
        $table = TableRegistry::get('CsvMigrations.Imports');
        $entity = $table->get($id);

        $path = $entity->get('filename');
        if ('processed' === $type) {
            $path = ImportUtility::getProcessedFile($entity);
        }

        return $this->response->withFile($path, ['download' => true]);
    }
}
