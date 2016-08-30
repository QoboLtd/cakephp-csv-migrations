<?php
namespace CsvMigrations\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Hash;
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
            ->contain(['DbListItems' => ['sort' => ['DbListItems.name' => 'ASC']]])
            ->first();
        if ($list) {
            $items = Hash::get($list, 'db_list_items') ?: [];
            foreach ($items as $entity) {
                $name = h($entity->get('name'));
                $value = h($entity->get('value'));
                $result[$name] = $value;
            }
        }

        return $result;
    }
}
