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

use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use CsvMigrations\Model\Entity\DblistItem;

/**
 * DblistItems Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Dblists
 *
 * @method DblistItem get($primaryKey, $options = [])
 * @method DblistItem newEntity($data = null, array $options = [])
 * @method DblistItem[] newEntities(array $data, array $options = [])
 * @method DblistItem|bool save(EntityInterface $entity, $options = [])
 * @method DblistItem patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method DblistItem[] patchEntities($entities, array $data, array $options = [])
 * @method DblistItem findOrCreate($search, callable $callback = null)
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
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('dblist_items');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Tree');

        $this->belongsTo('Dblists', [
            'foreignKey' => 'dblist_id',
            'joinType' => 'INNER',
            'className' => 'CsvMigrations.Dblists',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
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

        $validator
            ->requirePresence('dblist_id', 'create')
            ->notEmpty('dblist_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['dblist_id'], 'Dblists'));
        $rules->add(
            $rules->isUnique(
                ['dblist_id', 'name', 'value'],
                __d('Qobo/CsvMigrations', 'This list item is already in this list.')
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
     * @see  \Cake\ORM\Behavior\TreeBehavior::findTreeList
     * @param \Cake\Datasource\QueryInterface $query The query
     * @param mixed[] $options Query options
     * @return \Cake\Datasource\QueryInterface
     */
    public function findTreeEntities(QueryInterface $query, array $options): QueryInterface
    {
        $query = $query->where(['dblist_id' => $options['listId']])
            ->order(['lft' => 'asc']);

        // workaround for getting spacer.
        $tree = $this->find('treeList', ['spacer' => '&nbsp;&nbsp;&nbsp;&nbsp;'])
                ->toArray();

        foreach ($query->all() as $item) {
            $id = $item->get('id');
            if (in_array($id, array_keys($tree))) {
                $item->set('spacer', $tree[$id]);
            }
        }

        return $query;
    }
}
