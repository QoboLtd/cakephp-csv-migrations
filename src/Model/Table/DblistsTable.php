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
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use CsvMigrations\Model\Entity\Dblist;
use Webmozart\Assert\Assert;

/**
 * Dblists Model
 *
 * @property \Cake\ORM\Association\HasMany $DblistItems
 *
 * @method Dblist get($primaryKey, $options = [])
 * @method Dblist newEntity($data = null, array $options = [])
 * @method Dblist[] newEntities(array $data, array $options = [])
 * @method Dblist|bool save(EntityInterface $entity, $options = [])
 * @method Dblist patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method Dblist[] patchEntities($entities, array $data, array $options = [])
 * @method Dblist findOrCreate($search, callable $callback = null)
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
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('dblists');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('DblistItems', [
            'foreignKey' => 'dblist_id',
            'className' => 'CsvMigrations.DblistItems',
            'dependent' => true,
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
            ->notEmpty('name')
            ->add(
                'name',
                'unique',
                [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                    'message' => __d('Qobo/CsvMigrations', 'Name MUST be unique'),
                ]
            );

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
     * @param string $listName List name to retrieve options from
     * @return mixed[] Options for the select option field
     */
    public function getOptions(string $listName): array
    {
        try {
            $entity = $this->find('all')
                ->enableHydration(true)
                ->where(['name' => $listName])
                ->firstOrFail();
        } catch (RecordNotFoundException $e) {
            return [];
        }

        $treeOptions = [
            'keyPath' => 'value',
            'valuePath' => 'name',
            'spacer' => ' - ',
        ];

        Assert::isInstanceOf($entity, EntityInterface::class);

        return $this->DblistItems->find('treeList', $treeOptions)
            ->where(['dblist_id' => $entity->get('id')])
            ->toArray();
    }
}
