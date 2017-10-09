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
namespace CsvMigrations\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ImportResults Model
 *
 * @property \CsvMigrations\Model\Table\ImportsTable|\Cake\ORM\Association\BelongsTo $Imports
 *
 * @method \CsvMigrations\Model\Entity\ImportResult get($primaryKey, $options = [])
 * @method \CsvMigrations\Model\Entity\ImportResult newEntity($data = null, array $options = [])
 * @method \CsvMigrations\Model\Entity\ImportResult[] newEntities(array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\ImportResult|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CsvMigrations\Model\Entity\ImportResult patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\ImportResult[] patchEntities($entities, array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\ImportResult findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ImportResultsTable extends Table
{
    /**
     * Success status.
     */
    const STATUS_SUCCESS = 'Success';

    /**
     * Fail status.
     */
    const STATUS_FAIL = 'Fail';

    /**
     * Pending status.
     */
    const STATUS_PENDING = 'Pending';

    /**
     * Success status message.
     */
    const STATUS_SUCCESS_MESSAGE = 'Imported successfully';

    /**
     * Fail status message.
     */
    const STATUS_FAIL_MESSAGE = 'Import failed %s';

    /**
     * Pending status message.
     */
    const STATUS_PENDING_MESSAGE = 'Pending import';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('import_results');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Muffin/Trash.Trash');
        $this->addBehavior('Timestamp');

        $this->belongsTo('Imports', [
            'foreignKey' => 'import_id',
            'joinType' => 'INNER',
            'className' => 'CsvMigrations.Imports'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->uuid('id')
            ->allowEmpty('id', 'create');

        $validator
            ->integer('row_number')
            ->requirePresence('row_number', 'create')
            ->notEmpty('row_number');

        $validator
            ->requirePresence('model_name', 'update')
            ->notEmpty('model_name', 'update');

        $validator
            ->requirePresence('model_id', 'update')
            ->notEmpty('model_id', 'update');

        $validator
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        $validator
            ->requirePresence('status_message', 'create')
            ->notEmpty('status_message');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['import_id'], 'Imports'));

        return $rules;
    }

    /**
     * Find import results by import id and success status.
     *
     * @param \Cake\ORM\Query $query Query object
     * @param array $options Additional options
     * @return \Cake\ORM\Query
     */
    public function findImported(Query $query, array $options)
    {
        $query->where([
            'import_id' => $options['import']->id,
            'status' => static::STATUS_SUCCESS
        ]);

        return $query;
    }

    /**
     * Find import results by import id and pending status.
     *
     * @param \Cake\ORM\Query $query Query object
     * @param array $options Additional options
     * @return \Cake\ORM\Query
     */
    public function findPending(Query $query, array $options)
    {
        $query->where([
            'import_id' => $options['import']->id,
            'status' => static::STATUS_PENDING
        ]);

        return $query;
    }

    /**
     * Find import results by import id and fail status.
     *
     * @param \Cake\ORM\Query $query Query object
     * @param array $options Additional options
     * @return \Cake\ORM\Query
     */
    public function findFailed(Query $query, array $options)
    {
        $query->where([
            'import_id' => $options['import']->id,
            'status' => static::STATUS_FAIL
        ]);

        return $query;
    }
}
