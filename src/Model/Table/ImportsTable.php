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

use Cake\Database\Schema\TableSchema;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Imports Model
 *
 * @property \CsvMigrations\Model\Table\ImportResultsTable|\Cake\ORM\Association\HasMany $ImportResults
 *
 * @method \CsvMigrations\Model\Entity\Import get($primaryKey, $options = [])
 * @method \CsvMigrations\Model\Entity\Import newEntity($data = null, array $options = [])
 * @method \CsvMigrations\Model\Entity\Import[] newEntities(array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\Import|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CsvMigrations\Model\Entity\Import patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\Import[] patchEntities($entities, array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\Import findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ImportsTable extends Table
{
    /**
     * Completed status.
     */
    const STATUS_COMPLETED = 'Completed';

    /**
     * Fail status.
     */
    const STATUS_FAIL = 'Fail';

    /**
     * Pending status.
     */
    const STATUS_PENDING = 'Pending';

    /**
     * In progress status.
     */
    const STATUS_IN_PROGRESS = 'In progress';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('imports');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Muffin/Trash.Trash');
        $this->addBehavior('Timestamp');

        $this->hasMany('ImportResults', [
            'foreignKey' => 'import_id',
            'className' => 'CsvMigrations.ImportResults'
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
            ->requirePresence('filename', 'create')
            ->notEmpty('filename');

        $validator
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        $validator
            ->requirePresence('model_name', 'create')
            ->notEmpty('model_name');

        $validator
            ->requirePresence('attempts', 'create')
            ->notEmpty('attempts');

        $validator
            ->dateTime('trashed')
            ->allowEmpty('trashed');

        return $validator;
    }

    /**
     * {@inheritDoc}
     */
    protected function _initializeSchema(TableSchema $schema)
    {
        $schema->columnType('options', 'json');

        return $schema;
    }
}
