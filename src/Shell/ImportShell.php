<?php
namespace CsvMigrations\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;
use Cake\Shell\Helper\ProgressHelper;
use CsvMigrations\Controller\Traits\ImportTrait;
use CsvMigrations\Model\Entity\Import;
use CsvMigrations\Model\Entity\ImportResult;
use CsvMigrations\Model\Table\ImportsTable;
use CsvMigrations\Utility\Import as ImportUtility;
use Exception;
use League\Csv\Reader;
use Qobo\Utils\Utility\FileLock;

class ImportShell extends Shell
{
    use ImportTrait;

    /**
     * Set shell description and command line options
     *
     * @return ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');
        $parser->description('Validate CSV and configuration files of all CSV modules');

        return $parser;
    }

    /**
     * Main method for shell execution
     *
     * @return void
     */
    public function main()
    {
        try {
            $lock = new FileLock('import.lock');
        } catch (Exception $e) {
            $this->abort($e->getMessage());
        }

        if (!$lock->lock()) {
            $this->abort('Import is already in progress');
        }

        $this->out('Data Importing');
        $this->hr();

        $table = TableRegistry::get('CsvMigrations.Imports');
        $query = $table->find('all')
            ->where([
                'status IN' => [$table->getStatusPending(), $table->getStatusInProgress()],
                'options IS NOT' => null
            ]);

        if ($query->isEmpty()) {
            $this->abort('No imports found.');
        }

        $progress = $this->helper('Progress');
        $progress->init();
        $progressCount = 0;
        foreach ($query->all() as $import) {
            // exclude first row from count
            $progressCount -= 1;
            $reader = Reader::createFromPath($import->filename, 'r');
            $progressCount += $reader->each(function ($row) {
                return true;
            });
        }

            $this->out('Importing from file: ' . basename($import->get('filename')));
            $this->hr();

            $this->info('Preparing records ..');
            // skip if failed to generate import results records
            if (!$this->createImportResults($import, $count)) {
                continue;
            }

        foreach ($query->all() as $import) {
            // new import
            if ($table->getStatusPending() === $import->get('status')) {
                $this->_newImport($table, $import, $progress, $progressCount);
            }

            // in progress import
            if ($table->getStatusInProgress() === $import->get('status')) {
                $this->_existingImport($table, $import, $progress, $progressCount);
            }
        }

        // unlock file
        $lock->unlock();
    }

    /**
     * Import results generator.
     *
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @param int $count Progress count
     * @return bool
     */
    protected function createImportResults(Import $import, $count)
    {
        $progress = $this->helper('Progress');
        $progress->init();

        if (0 >= $count) {
            return false;
        }

        $table = TableRegistry::get('CsvMigrations.ImportResults');

        $data = [
            'import_id' => $import->get('id'),
            'status' => $table->getStatusPending(),
            'status_message' => $table->getStatusPendingMessage(),
            'model_name' => $import->get('model_name')
        ];

        // set $i = 1 to skip header row
        for ($i = 1; $i < $count; $i++) {
            $data['row_number'] = $i;

            $entity = $table->newEntity();
            $entity = $table->patchEntity($entity, $data);

            $table->save($entity);

            $progress->increment(100 / $count);
            $progress->draw();
        }
        $this->out(null);

        return true;
    }

    /**
     * New import.
     *
     * @param \CsvMigrations\Model\Entity\Import $table Table object
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @param \Cake\Shell\Helper\ProgressHelper $progress Progress Helper
     * @param int $count Progress count
     * @return bool
     */
    protected function _newImport(ImportsTable $table, Import $import, ProgressHelper $progress, $count)
    {
        $data = [
            'status' => $table->getStatusInProgress(),
            'attempts' => 1,
            'attempted_date' => Time::now()
        ];

        $import = $table->patchEntity($import, $data);
        $table->save($import);

        $this->_run($import, $progress, $count);

        // mark import as completed
        $data = [
            'status' => $table->getStatusCompleted()
        ];

        $import = $table->patchEntity($import, $data);

        return $table->save($import);
    }

    /**
     * Existing import.
     *
     * @param \CsvMigrations\Model\Entity\Import $table Table object
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @param \Cake\Shell\Helper\ProgressHelper $progress Progress Helper
     * @param int $count Progress count
     * @return bool
     */
    protected function _existingImport(ImportsTable $table, Import $import, ProgressHelper $progress, $count)
    {
        $result = false;

        $data = ['attempted_date' => Time::now()];

        // max attempts rearched
        if ($import->get('attempts') >= (int)Configure::read('Importer.max_attempts')) {
            // set import as failed
            $data['status'] = $table->getStatusFail();
            $import = $table->patchEntity($import, $data);
            $result = $table->save($import);
        } else {
            // increase attempts count
            $data['attempts'] = $import->get('attempts') + 1;
            $import = $table->patchEntity($import, $data);
            $table->save($import);

            $this->_run($import, $progress, $count);

            // mark import as completed
            $data['status'] = $table->getStatusCompleted();
            $import = $table->patchEntity($import, $data);
            $result = $table->save($import);
        }

        return $result;
    }

    /**
     * Run data import.
     *
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @param \Cake\Shell\Helper\ProgressHelper $progress Progress Helper
     * @param int $count Progress count
     * @return void
     */
    protected function _run(Import $import, ProgressHelper $progress, $count)
    {
        $reader = Reader::createFromPath($import->filename, 'r');

        $columns = $this->_getColumns($import);

        foreach ($reader as $index => $row) {
            // skip first csv row
            if (0 === $index) {
                continue;
            }

            $this->_importResult($import->get('id'), $index, $row, $columns);

            $progress->increment(100 / $count);
            $progress->draw();
        }
    }

    /**
     * Get import columns from Import options.
     *
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @return array
     */
    protected function _getColumns(Import $import)
    {
        $result = [];

        $headers = ImportUtility::getUploadHeaders($import);
        if (empty($headers)) {
            return $result;
        }

        $options = $import->get('options');
        foreach ($headers as $index => $header) {
            // skip non-mapped headers
            if (!array_key_exists($header, $options)) {
                continue;
            }

            $result[$index] = $header;
        }

        return $result;
    }

    /**
     * Import row.
     *
     * @param string $id Import id
     * @param int $rowNumber Current row number
     * @param array $data Row data
     * @param array $columns Import columns
     * @return void
     */
    protected function _importResult($id, $rowNumber, array $data, array $columns)
    {
        $importTable = TableRegistry::get('CsvMigrations.ImportResults');
        $query = $importTable->find('all')->where(['import_id' => $id, 'row_number' => $rowNumber]);
        $importResult = $query->first();

        // skip successful imports
        if ('Success' === $importResult->get('status')) {
            return;
        }

        $data = array_intersect_key($data, $columns);
        ksort($data);
        $data = array_combine($columns, $data);

        $table = TableRegistry::get($importResult->get('model_name'));
        $entity = $table->newEntity();
        try {
            $entity = $table->patchEntity($entity, $data);
        } catch (Exception $e) {
            $this->_importFail($importResult, $e->getMessage());

            continue;
        }

        if ($table->save($entity)) {
            $this->_importSuccess($importResult, $entity);
        } else {
            $this->_importFail($importResult, $entity->getErrors());
        }
    }

    /**
     * Mark import result as failed.
     *
     * @param \CsvMigrations\Model\Entity\ImportResult $entity ImportResult entity
     * @param mixed $errors Save errors
     * @return bool
     */
    protected function _importFail(ImportResult $entity, $errors)
    {
        $table = TableRegistry::get('CsvMigrations.ImportResults');

        $errors = json_encode($errors);

        $message = printf($table->getStatusFailMessage(), $errors);
        $entity->set('status', $table->getStatusFail());
        $entity->set('status_message', $message);

        return $table->save($entity);
    }

    /**
     * Mark import result as successful.
     *
     * @param \CsvMigrations\Model\Entity\ImportResult $importResult ImportResult entity
     * @param \Cake\ORM\Entity $entity Newly created Entity
     * @return bool
     */
    protected function _importSuccess(ImportResult $importResult, Entity $entity)
    {
        $table = TableRegistry::get('CsvMigrations.ImportResults');

        $importResult->set('model_id', $entity->get('id'));
        $importResult->set('status', $table->getStatusSuccess());
        $importResult->set('status_message', $table->getStatusSuccessMessage());

        return $table->save($importResult);
    }
}
