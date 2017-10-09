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
 * DblistItems Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Dblists
 *
 * @method \CsvMigrations\Model\Entity\DblistItem get($primaryKey, $options = [])
 * @method \CsvMigrations\Model\Entity\DblistItem newEntity($data = null, array $options = [])
 * @method \CsvMigrations\Model\Entity\DblistItem[] newEntities(array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\DblistItem|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CsvMigrations\Model\Entity\DblistItem patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\DblistItem[] patchEntities($entities, array $data, array $options = [])
 * @method \CsvMigrations\Model\Entity\DblistItem findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DblistItemsTable extends Table
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

        $this->table('dblist_items');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Tree');

        $this->belongsTo('Dblists', [
            'foreignKey' => 'dblist_id',
            'joinType' => 'INNER',
            'className' => 'CsvMigrations.Dblists'
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
        $rules->add($rules->existsIn(['dblist_id'], 'Dblists'));
        $rules->add(
            $rules->isUnique(
                ['dblist_id', 'name', 'value'],
                __d('CsvMigrations', 'This list item is already in this list.')
            )
        );

        return $rules;
    }

    /**
     * Return all the entities along with the spacer from the treeList.
     *
     * Options should be
     * - listId: List Id to fetch its items.
     *
     * @see  Cake\ORM\Behavior\TreeBehavior::findTreeList
     * @param  Query  $query   [description]
     * @param  array  $options [description]
     * @return [type]          [description]
     */
    public function findTreeEntities(Query $query, array $options)
    {
        $query = $query
            ->where(['dblist_id' => $options['listId']])
            ->order(['lft' => 'asc']);
        //Workaround for getting spacer.
        $tree = $this->find('treeList', ['spacer' => '&nbsp;&nbsp;&nbsp;&nbsp;'])
                ->toArray();
        foreach ($query as $item) {
            $id = $item->get('id');
            if (in_array($id, array_keys($tree))) {
                $item->set('spacer', $tree[$id]);
            }
        }

        return $query;
    }
}
