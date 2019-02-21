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
namespace CsvMigrations\FieldHandlers;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\RepositoryInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use RuntimeException;
use Webmozart\Assert\Assert;

trait RelatedFieldTrait
{
    /**
     * Field value separator
     *
     * @var string
     */
    protected $_separator = '»';

    /**
     * Get related model's parent model properties.
     *
     * @param mixed[] $relatedProperties related model properties
     * @return mixed[] $result containing parent properties
     */
    protected function _getRelatedParentProperties(array $relatedProperties) : array
    {
        if (empty($relatedProperties['entity']) ||
            empty($relatedProperties['controller']) ||
            empty($relatedProperties['config']['parent']['module'])
        ) {
            return [];
        }

        $foreignKey = $this->_getForeignKey(
            TableRegistry::get($relatedProperties['config']['parent']['module']),
            empty($relatedProperties['plugin']) ?
                $relatedProperties['controller'] :
                sprintf('%s.%s', $relatedProperties['plugin'], $relatedProperties['controller'])
        );

        if ('' === $foreignKey) {
            return [];
        }

        if (empty($relatedProperties['entity']->get($foreignKey))) {
            return [];
        }

        $result = $this->_getRelatedProperties(
            $relatedProperties['config']['parent']['module'],
            $relatedProperties['entity']->get($foreignKey)
        );

        if (null === $result['entity']) {
            return [];
        }

        return $result;
    }

    /**
     * Get related model's properties.
     *
     * @param string $tableName Related table name
     * @param string $data query parameter value
     * @return mixed[]
     */
    protected function _getRelatedProperties(string $tableName, string $data) : array
    {
        $table = TableRegistry::get($tableName);

        $config = (new ModuleConfig(ConfigType::MODULE(), $tableName))->parseToArray();

        $displayField = $table->getDisplayField();
        $displayFieldValue = '';

        try {
            $entity = $this->_getAssociatedRecord($table, $data);

            // get related table's display field value by rendering it through field handler factory
            $value = (new FieldHandlerFactory())->renderValue(
                $table,
                $displayField,
                $entity->get($displayField),
                ['renderAs' => Setting::RENDER_PLAIN_VALUE_RELATED()]
            );
            $displayFieldValue = '' === $value ? 'N/A' : $value;
        } catch (RecordNotFoundException $e) {
            // @todo rethrow the exception
            $entity = null;
        }

        $result = [
            'id' => $data,
            'config' => $config,
            'displayField' => $displayField,
            'entity' => $entity,
            'dispFieldVal' => $displayFieldValue
        ];

        // get plugin and controller names
        list($result['plugin'], $result['controller']) = pluginSplit($table->getAlias());

        return $result;
    }

    /**
     * Get parent model association's foreign key.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $modelName Model name
     * @return string
     */
    protected function _getForeignKey(Table $table, string $modelName) : string
    {
        foreach ($table->associations() as $association) {
            if ($modelName !== $association->className()) {
                continue;
            }

            $primaryKey = $association->getForeignKey();
            if (! is_string($primaryKey)) {
                throw new RuntimeException('Primary key must be a string');
            }

            return $primaryKey;
        }

        return '';
    }

    /**
     * Retrieve and return associated record Entity, by primary key value.
     * If the record has been trashed - query will return NULL.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $value Primary key value
     * @return \Cake\Datasource\EntityInterface
     */
    protected function _getAssociatedRecord(Table $table, string $value) : EntityInterface
    {
        $primaryKey = $table->getPrimaryKey();
        if (! is_string($primaryKey)) {
            throw new RuntimeException('Primary key must be a string');
        }

        // try to fetch with trashed if finder method exists, otherwise fallback to find all
        $finderMethod = $table->hasBehavior('Trash') ? 'withTrashed' : 'all';

        $entity = $table->find($finderMethod, ['conditions' => [$table->aliasField($primaryKey) => $value]])
            ->enableHydration(true)
            ->firstOrFail();
        Assert::isInstanceOf($entity, EntityInterface::class);

        return $entity;
    }

    /**
     * Generate input help string
     *
     * Can be used as a value for placeholder or title attributes.
     *
     * @param mixed[] $properties Input properties
     * @return string
     */
    protected function _getInputHelp(array $properties) : string
    {
        $config = (new ModuleConfig(ConfigType::MODULE(), $properties['controller']))->parseToArray();
        $typeaheadFields = !empty($config['table']['typeahead_fields']) ? $config['table']['typeahead_fields'] : [];
        // if no typeahead fields, use display field
        if (empty($typeaheadFields)) {
            $typeaheadFields = [$properties['displayField']];
        }

        $virtualFields = !empty($config['virtualFields']) ? $config['virtualFields'] : [];

        // extract virtual fields, if any
        $result = [];
        foreach ($typeaheadFields as $typeaheadField) {
            $fields = isset($virtualFields[$typeaheadField]) ?
                (array)$virtualFields[$typeaheadField] :
                [$typeaheadField];

            $result = array_merge($result, $fields);
        }

        return implode(', or ', array_map(function ($value) {
            return Inflector::humanize($value);
        }, $result));
    }

    /**
     * Get input field associated icon
     *
     * @param mixed[] $properties Input properties
     * @return string
     */
    protected function _getInputIcon(array $properties) : string
    {
        $config = (new ModuleConfig(ConfigType::MODULE(), $properties['controller']))->parseToArray();

        if (! isset($config['table'])) {
            return Configure::read('CsvMigrations.default_icon');
        }

        if (! isset($config['table']['icon'])) {
            return Configure::read('CsvMigrations.default_icon');
        }

        return $config['table']['icon'];
    }
}
