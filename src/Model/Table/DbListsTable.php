<?php
namespace CsvMigrations\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DbLists Model
 *
 * @property \Cake\ORM\Association\HasMany $DbListItems
 *
 * @method \CsvMigrations\Model\Entity\DbList get($primaryKey, $options = [])
 * @method \CsvMigrations\Model\Entity\DbList newEntity($data = null, array $options = [])
 * @method \CsvMigrations\Model\Entity\DbList[] newEntities(array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\DbList|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CsvMigrations\Model\Entity\DbList patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\DbList[] patchEntities($entities, array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\DbList findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DbListsTable extends Table
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

        $this->table('db_lists');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('DbListItems', [
            'foreignKey' => 'db_list_id',
            'className' => 'CsvMigrations.DbListItems'
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

        return $validator;
    }
}
