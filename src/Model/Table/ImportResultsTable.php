<?php
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
            ->requirePresence('model_name', 'create')
            ->notEmpty('model_name');

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
}
