<?php
namespace CsvMigrations\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DbListItems Model
 *
 * @property \Cake\ORM\Association\BelongsTo $DbLists
 *
 * @method \CsvMigrations\Model\Entity\DbListItem get($primaryKey, $options = [])
 * @method \CsvMigrations\Model\Entity\DbListItem newEntity($data = null, array $options = [])
 * @method \CsvMigrations\Model\Entity\DbListItem[] newEntities(array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\DbListItem|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CsvMigrations\Model\Entity\DbListItem patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\DbListItem[] patchEntities($entities, array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\DbListItem findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DbListItemsTable extends Table
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

        $this->table('db_list_items');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('DbLists', [
            'foreignKey' => 'db_list_id',
            'joinType' => 'INNER',
            'className' => 'CsvMigrations.DbLists'
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
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->requirePresence('value', 'create')
            ->notEmpty('value');

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
        $rules->add($rules->existsIn(['db_list_id'], 'DbLists'));

        return $rules;
    }
}
