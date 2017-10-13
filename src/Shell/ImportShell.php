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
namespace CsvMigrations\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Shell\Helper\ProgressHelper;
use CsvMigrations\Controller\Traits\ImportTrait;
use CsvMigrations\FieldTrait;
use CsvMigrations\Model\Entity\Import;
use CsvMigrations\Model\Entity\ImportResult;
use CsvMigrations\Model\Table\ImportsTable;
use CsvMigrations\Utility\Field as FieldUtility;
use CsvMigrations\Utility\Import as ImportUtility;
use Exception;
use League\Csv\Reader;
use League\Csv\Writer;
use Qobo\Utils\Utility\FileLock;

class ImportShell extends Shell
{
    use FieldTrait;
    use ImportTrait;

    /**
     * Set shell description and command line options
     *
     * @return ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');
        $parser->description('Process all import jobs');

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
            $lock = new FileLock('import_' . md5(__FILE__) . '.lock');
        } catch (Exception $e) {
            $this->abort($e->getMessage());
        }

        if (!$lock->lock()) {
            $this->abort('Import is already in progress');
        }

        $table = TableRegistry::get('CsvMigrations.Imports');
        $query = $table->find('all')
            ->where([
                'status IN' => [$table::STATUS_PENDING, $table::STATUS_IN_PROGRESS],
                'options IS NOT' => null,
                'options !=' => '',
            ]);

        if ($query->isEmpty()) {
            // unlock file
            $lock->unlock();

            $this->abort('No imports found');
        }

        foreach ($query->all() as $import) {
            $path = ImportUtility::getProcessedFile($import);
            $filename = ImportUtility::getProcessedFile($import, false);

            $this->out('Importing from file: "' . $filename . '"');

            // process import file
            $this->processImportFile($import);

            if (empty($import->get('options'))) {
                $this->warn('Skipping, no mapping found for file:' . $filename);
                $this->hr();
                continue;
            }

            $count = ImportUtility::getRowsCount($path);

            // new import
            if ($table::STATUS_PENDING === $import->get('status')) {
                $this->_newImport($table, $import, $count);
            }

            // in progress import
            if ($table::STATUS_IN_PROGRESS === $import->get('status')) {
                $this->_existingImport($table, $import, $count);
            }
            $this->hr();
        }

        $this->success('Import Completed');

        // unlock file
        $lock->unlock();
    }

    /**
     * Process import file.
     *
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @return void
     */
    protected function processImportFile(Import $import)
    {
        $this->info('Processing import file ..');

        $path = ImportUtility::getProcessedFile($import);
        if (file_exists($path)) {
            return;
        }

        // create processed file
        $writer = Writer::createFromPath($path, 'w+');

        $reader = Reader::createFromPath($import->get('filename'), 'r');

        $results = $reader->fetch();
        foreach ($results as $row) {
            if (empty(array_filter($row))) {
                continue;
            }

            $writer->insertOne($row);
        }
    }

    /**
     * New import.
     *
     * @param \CsvMigrations\Model\Entity\Import $table Table object
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @param int $count Progress count
     * @return bool
     */
    protected function _newImport(ImportsTable $table, Import $import, $count)
    {
        $data = [
            'status' => $table::STATUS_IN_PROGRESS,
            'attempts' => 1,
            'attempted_date' => Time::now()
        ];

        $import = $table->patchEntity($import, $data);
        $table->save($import);

        $this->_run($import, $count);

        // mark import as completed
        $data = [
            'status' => $table::STATUS_COMPLETED
        ];

        $import = $table->patchEntity($import, $data);

        return $table->save($import);
    }

    /**
     * Existing import.
     *
     * @param \CsvMigrations\Model\Entity\Import $table Table object
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @param int $count Progress count
     * @return bool
     */
    protected function _existingImport(ImportsTable $table, Import $import, $count)
    {
        $result = false;

        $data = ['attempted_date' => Time::now()];

        // max attempts rearched
        if ($import->get('attempts') >= (int)Configure::read('Importer.max_attempts')) {
            // set import as failed
            $data['status'] = $table::STATUS_FAIL;
            $import = $table->patchEntity($import, $data);
            $result = $table->save($import);

            return $result;
        }

        // increase attempts count
        $data['attempts'] = $import->get('attempts') + 1;
        $import = $table->patchEntity($import, $data);
        $table->save($import);

        $this->_run($import, $count);

        // mark import as completed
        $data['status'] = $table::STATUS_COMPLETED;
        $import = $table->patchEntity($import, $data);
        $result = $table->save($import);

        return $result;
    }

    /**
     * Run data import.
     *
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @param int $count Progress count
     * @return void
     */
    protected function _run(Import $import, $count)
    {
        // generate import results records
        $this->createImportResults($import, $count);

        $this->info('Importing records ..');
        $progress = $this->helper('Progress');
        $progress->init();

        $headers = ImportUtility::getUploadHeaders($import);
        $filename = ImportUtility::getProcessedFile($import);
        $reader = Reader::createFromPath($filename, 'r');
        foreach ($reader as $index => $row) {
            // skip first csv row
            if (0 === $index) {
                continue;
            }

            // skip empty row
            if (empty($row)) {
                continue;
            }

            $this->_importResult($import, $headers, $index, $row);

            $progress->increment(100 / $count);
            $progress->draw();
        }
        $this->out(null);
    }

    /**
     * Import results generator.
     *
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @param int $count Progress count
     * @return void
     */
    protected function createImportResults(Import $import, $count)
    {
        $this->info('Preparing records ..');

        $progress = $this->helper('Progress');
        $progress->init();

        $table = TableRegistry::get('CsvMigrations.ImportResults');

        $query = $table->find('all')->where(['import_id' => $import->get('id')]);
        $queryCount = $query->count();

        if ($queryCount >= $count) {
            return;
        }

        $data = [
            'import_id' => $import->get('id'),
            'status' => $table::STATUS_PENDING,
            'status_message' => $table::STATUS_PENDING_MESSAGE,
            'model_name' => $import->get('model_name')
        ];

        $i = $queryCount + 1;
        $progressCount = $count - $queryCount;
        // set $i = 1 to skip header row
        for ($i; $i <= $count; $i++) {
            $data['row_number'] = $i;

            $entity = $table->newEntity();
            $entity = $table->patchEntity($entity, $data);

            $table->save($entity);

            $progress->increment(100 / $progressCount);
            $progress->draw();
        }

        $this->out(null);
    }

    /**
     * Import row.
     *
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @param array $headers Upload file headers
     * @param int $rowNumber Current row number
     * @param array $data Row data
     * @return void
     */
    protected function _importResult(Import $import, array $headers, $rowNumber, array $data)
    {
        $importTable = TableRegistry::get('CsvMigrations.ImportResults');
        $query = $importTable->find('all')->where(['import_id' => $import->get('id'), 'row_number' => $rowNumber]);
        $importResult = $query->first();

        // skip successful imports
        if ($importTable::STATUS_SUCCESS === $importResult->get('status')) {
            return;
        }

        $table = TableRegistry::get($importResult->get('model_name'));

        $data = $this->_prepareData($import, $headers, $data);
        $csvFields = FieldUtility::getCsv($table);
        $data = $this->_processData($table, $csvFields, $data);

        // skip empty processed data
        if (empty($data)) {
            $this->_importFail($importResult, 'Row has no data');

            return;
        }

        $entity = $table->newEntity();
        try {
            $entity = $table->patchEntity($entity, $data);
        } catch (Exception $e) {
            $this->_importFail($importResult, $e->getMessage());

            return;
        }

        if ($table->save($entity)) {
            $this->_importSuccess($importResult, $entity);
        } else {
            $this->_importFail($importResult, $entity->getErrors());
        }
    }

    /**
     * Prepare row data.
     *
     * @param \CsvMigrations\Model\Entity\Import $import Import entity
     * @param array $headers Upload file headers
     * @param array $data Row data
     * @return array
     */
    protected function _prepareData(Import $import, array $headers, array $data)
    {
        $result = [];

        $options = $import->get('options');

        $flipped = array_flip($headers);

        foreach ($options['fields'] as $field => $params) {
            if (empty($params['column']) && empty($params['default'])) {
                continue;
            }

            if (array_key_exists($params['column'], $flipped)) {
                $value = $data[$flipped[$params['column']]];
                if (!empty($value)) {
                    $result[$field] = $value;
                    continue;
                }
            }

            if (!empty($params['default'])) {
                $result[$field] = $params['default'];
            }
        }

        return $result;
    }

    /**
     * Process row data.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param array $csvFields Table csv fields
     * @param array $data Entity data
     * @return array
     */
    protected function _processData(Table $table, array $csvFields, array $data)
    {
        $result = [];

        $schema = $table->schema();

        foreach ($data as $field => $value) {
            if (!empty($csvFields)) {
                switch ($csvFields[$field]->getType()) {
                    case 'related':
                        $data[$field] = $this->_findRelatedRecord($table, $field, $value);
                        break;
                    case 'list':
                        $data[$field] = $this->_findListValue($csvFields[$field]->getLimit(), $value);
                        break;
                }
            } else {
                if ('uuid' === $schema->columnType($field)) {
                    $data[$field] = $this->_findRelatedRecord($table, $field, $value);
                }
            }
        }

        return $data;
    }

    /**
     * Fetch related record id if found, otherwise return initial value.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $field Field name
     * @param string $value Field value
     * @return string
     */
    protected function _findRelatedRecord(Table $table, $field, $value)
    {
        foreach ($table->associations() as $association) {
            if ($association->getForeignKey() !== $field) {
                continue;
            }

            $targetTable = $association->getTarget();

            $primaryKey = $targetTable->getPrimaryKey();

            $lookupFields = FieldUtility::getLookup($targetTable);
            $lookupFields[] = $primaryKey;
            // alias lookup fields
            foreach ($lookupFields as $k => $v) {
                $lookupFields[$k] = $targetTable->aliasField($v);
            }

            // populate lookup field values
            $lookupValues = array_fill(0, count($lookupFields), $value);

            $query = $targetTable->find('all')
                ->select([$targetTable->aliasField($primaryKey)])
                ->where(['OR' => array_combine($lookupFields, $lookupValues)]);

            if ($query->isEmpty()) {
                continue;
            }

            $entity = $query->first();

            return $entity->get($primaryKey);
        }

        return $value;
    }

    /**
     * Fetch list value.
     *
     * First will try to find if the row value matches one
     * of the list options.
     *
     * @param string $listName List name
     * @param string $value Field value
     * @return string
     */
    protected function _findListValue($listName, $value)
    {
        $options = FieldUtility::getList($listName, true);

        // check against list options values
        foreach ($options as $val => $params) {
            if ($val !== $value) {
                continue;
            }

            return $val;
        }

        // check against list options labels
        foreach ($options as $val => $params) {
            if ($params['label'] !== $value) {
                continue;
            }

            return $val;
        }

        return $value;
    }

    /**
     * Mark import result as failed.
     *
     * @param \CsvMigrations\Model\Entity\ImportResult $entity ImportResult entity
     * @param mixed $errors Fail errors
     * @return bool
     */
    protected function _importFail(ImportResult $entity, $errors)
    {
        $table = TableRegistry::get('CsvMigrations.ImportResults');

        $errors = json_encode($errors);

        $entity->set('status', $table::STATUS_FAIL);
        $message = sprintf($table::STATUS_FAIL_MESSAGE, $errors);
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
        $importResult->set('status', $table::STATUS_SUCCESS);
        $importResult->set('status_message', $table::STATUS_SUCCESS_MESSAGE);

        return $table->save($importResult);
    }
}
