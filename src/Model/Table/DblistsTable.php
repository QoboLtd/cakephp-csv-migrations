<?php
namespace CsvMigrations\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Dblists Model
 *
 * @property \Cake\ORM\Association\HasMany $DblistItems
 *
 * @method \CsvMigrations\Model\Entity\Dblist get($primaryKey, $options = [])
 * @method \CsvMigrations\Model\Entity\Dblist newEntity($data = null, array $options = [])
 * @method \CsvMigrations\Model\Entity\Dblist[] newEntities(array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\Dblist|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CsvMigrations\Model\Entity\Dblist patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\Dblist[] patchEntities($entities, array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\Dblist findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DblistsTable extends Table
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

        $this->table('dblists');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('DblistItems', [
            'foreignKey' => 'dblist_id',
            'className' => 'CsvMigrations.DblistItems'
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
            ->notEmpty('name')
            ->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

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
        $rules->add($rules->isUnique(['name']));

        return $rules;
    }


    /**
     * Reusable query options.
     *
     * It can be used for retreving the options of the select field(list).
     * Options:
     * - name: List name (required)
     *
     * @param  Query $query   Query object
     * @param  array $options Options see function's long description.
     * @return array          Options for the select option field.
     */
    public function findOptions(Query $query, array $options)
    {
        $result = [];
        $name = Hash::get($options, 'name');
        $list = $this->find()
            ->where(['name' => $name])
            ->contain(['DblistItems' => ['sort' => ['DblistItems.name' => 'ASC']]])
            ->first();
        if ($list) {
            $items = Hash::get($list, 'dblist_items') ?: [];
            foreach ($items as $entity) {
                $name = h($entity->get('name'));
                $value = h($entity->get('value'));
                $result[$name] = $value;
            }
        }

        return $result;
    }
}
