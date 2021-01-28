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
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use CsvMigrations\Model\Entity\Import;

/**
 * Imports Model
 *
 * @property \CsvMigrations\Model\Table\ImportResultsTable|\Cake\ORM\Association\HasMany $ImportResults
 *
 * @method Import get($primaryKey, $options = [])
 * @method Import newEntity($data = null, array $options = [])
 * @method Import[] newEntities(array $data, array $options = [])
 * @method Import|bool save(EntityInterface $entity, $options = [])
 * @method Import patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method Import[] patchEntities($entities, array $data, array $options = [])
 * @method Import findOrCreate($search, callable $callback = null, $options = [])
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
        $this->addBehavior('Qobo/Utils.Footprint');

        $this->hasMany('ImportResults', [
            'foreignKey' => 'import_id',
            'className' => 'CsvMigrations.ImportResults',
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
            ->allowEmptyString('id', 'create');

        $validator
            ->requirePresence('filename', 'create')
            ->notEmptyString('filename');

        $validator
            ->requirePresence('status', 'create')
            ->notEmptyString('status');

        $validator
            ->requirePresence('model_name', 'create')
            ->notEmptyString('model_name');

        $validator
            ->requirePresence('attempts', 'create')
            ->notEmptyString('attempts');

        $validator
            ->dateTime('trashed')
            ->allowEmptyString('trashed');

        $validator
            ->uuid('created_by')
            ->notEmptyString('created_by');

        $validator
            ->uuid('modified_by')
            ->notEmptyString('modified_by');

        return $validator;
    }

    /**
     * {@inheritDoc}
     */
    protected function _initializeSchema(TableSchema $schema)
    {
        $schema->setColumnType('options', 'json');

        return $schema;
    }
}
