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

namespace CsvMigrations;

use ArrayObject;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Table as BaseTable;
use Cake\Validation\Validator;
use CsvMigrations\Event\EventName;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\Model\AssociationsAwareTrait;
use Qobo\Utils\Module\ModuleRegistry;
use Qobo\Utils\Utility\User;

/**
 * CsvMigrations Table
 *
 * All CSV modules should extend this Table
 * class for configuration and functionality.
 */
class Table extends BaseTable implements HasFieldsInterface
{
    use AssociationsAwareTrait;

    /**
     * Cached CSV field definitions for the current module
     *
     * @var array
     */
    protected $_fieldDefinitions = [];

    /**
     * Initialize
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->addBehavior('Qobo/Utils.Footprint');

        $moduleName = App::shortName($config['className'], 'Model/Table', 'Table');
        $module_config = ModuleRegistry::getModule($moduleName)->getConfig();

        // Set trash behavior
        if (!isset($module_config['table']['trash']) || $module_config['table']['trash'] === true) {
            $this->addBehavior('Muffin/Trash.Trash', isset($config['table']['trash']['events']) ? $config['table']['trash'] : []);
        }

        // set display field from config
        if (isset($module_config['table']['display_field'])) {
            $this->setDisplayField($module_config['table']['display_field']);
        }

        $this->setAssociations();
    }

    /**
     * Set Table validation rules
     *
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $className = App::shortName(get_class($this), 'Model/Table', 'Table');
        $config = ModuleRegistry::getModule($className)->getMigration();
        $factory = new FieldHandlerFactory();

        $factory = new FieldHandlerFactory();
        foreach ($config as $column) {
            $validator = $factory->setValidationRules($this, $column['name'], $validator);
        }

        return $validator;
    }

    /**
     * afterSave hook
     *
     * @param \Cake\Event\Event $event from the parent afterSave
     * @param \Cake\Datasource\EntityInterface $entity from the parent afterSave
     * @param \ArrayObject $options from the parent afterSave
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        EventManager::instance()->dispatch(new Event(
            (string)EventName::MODEL_AFTER_SAVE(),
            $this,
            ['entity' => $entity, 'options' => ['current_user' => User::getCurrentUser()]]
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function afterSaveCommit(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        EventManager::instance()->dispatch(new Event(
            (string)EventName::MODEL_AFTER_SAVE_COMMIT(),
            $this,
            ['entity' => $entity, 'options' => $options]
        ));
    }

    /**
     * Get fields from CSV file
     *
     * This method gets all fields defined in the CSV and returns
     * them as an associative array.
     *
     * Additionally, an associative array of stub fields can be
     * passed, which will be included in the returned definitions.
     * This is useful when working with fields which are NOT part
     * of the migration.csv definitions, such as combined fields
     * and virtual fields.
     *
     * If the field exists in the CSV configuration and is passed
     * as a stub field, then the CSV definition will be preferred.
     *
     * Note that this method is called very frequently during the
     * rendering of the views, so performance is important.  For
     * this reason, parsed definitions are stored in the property
     * to avoid unnecessary processing of files and conversion of
     * data. Stub fields, however, won't be cached as they are not
     * real definitions and might vary from call to call.
     *
     * There are cases, when no field definitions are available at
     * all.  For example, external, non-CSV modules.  For those
     * cases, all exceptions and errors are silenced and an empty
     * array of field definitions is returned.  Unless, of course,
     * there are stub fields provided.
     *
     * @param mixed[] $stubFields Stub fields
     * @return mixed[] Associative array of fields and their definitions
     */
    public function getFieldsDefinitions(array $stubFields = []): array
    {
        $result = [];

        // Get cached definitions
        if (! empty($this->_fieldDefinitions)) {
            $result = $this->_fieldDefinitions;
        }

        // Fetch definitions from CSV if cache is empty
        if (empty($result)) {
            $moduleName = App::shortName(get_class($this), 'Model/Table', 'Table');
            list(, $moduleName) = pluginSplit($moduleName);

            $config = ModuleRegistry::getModule($moduleName)->getMigration();
            if (!empty($config)) {
                $result = $this->_fieldDefinitions = $config;
            }
        }

        if (empty($stubFields)) {
            return $result;
        }

        // Merge $result with $stubFields
        foreach ($stubFields as $field => $definition) {
            if (!array_key_exists($field, $result)) {
                $result[$field] = $definition;
            }
        }

        return $result;
    }

    /**
     * getParentRedirectUrl
     *
     * Uses [parent] section of tables config.ini to define
     * where to redirect after the entity was added/edited.
     *
     * @param \Cake\ORM\Table $table of the entity table
     * @param \Cake\Datasource\EntityInterface $entity of the actual table.
     * @param string $parent identifying which config to pick
     *
     * @return mixed[] $result containing CakePHP-standard array for redirect.
     */
    public function getParentRedirectUrl(RepositoryInterface $table, EntityInterface $entity, string $parent): array
    {
        $config = ModuleRegistry::getModule($this->getAlias())->getMigration();
        if (! isset($config['parent']['redirect'])) {
            return [];
        }

        $parentConfig = [];

        foreach ($config['parent'] as $item) {
            if ($item['module'] === $parent) {
                $parentConfig = $item;
            }
        }

        if (empty($parentConfig)) {
            return [];
        }

        if ('parent' === $config['parent']['redirect']) {
            if (! isset($config['parent']['module'])) {
                return [];
            }

            if (! isset($config['parent']['relation'])) {
                return [];
            }

            return [
                'controller' => $parentConfig['module'],
                'action' => $entity->get($parentConfig['relation']) ? 'view' : 'index',
                $entity->get($parentConfig['relation']),
            ];
        }

        if ('self' === $parentConfig['redirect']) {
            $values = [];
            foreach ((array)$table->getPrimaryKey() as $primaryKey) {
                $values[] = $entity->get($primaryKey);
            }

            return array_merge(['action' => 'view'], $values);
        }

        return [];
    }

    /**
     * enablePrimaryKeyAccess
     *
     * Enable accessibility to associations primary key. Useful for
     * patching entities with associated data during updating process.
     *
     * @return mixed[]
     */
    public function enablePrimaryKeyAccess(): array
    {
        $result = [];
        foreach ($this->associations() as $association) {
            $accessibleFields = [];
            foreach ((array)$association->getTarget()->getPrimaryKey() as $primaryKey) {
                $accessibleFields[$primaryKey] = true;
            }

            $result['associated'][$association->getName()] = [
                'accessibleFields' => $accessibleFields,
            ];
        }

        return $result;
    }
}
