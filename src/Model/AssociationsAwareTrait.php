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
namespace CsvMigrations\Model;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\Utility\FileUpload;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\Utility;

/**
 * This Trait is responsible for automagically generating Model/Table associations,
 *  based on the defined Modules configuration files. The source code is heavily
 *  relying on fields defined as "related" type.
 */
trait AssociationsAwareTrait
{
    /**
     * Set Model/Table associations.
     *
     * @return void
     */
    public function setAssociations()
    {
        foreach (Utility::findDirs(Configure::read('CsvMigrations.modules.path')) as $module) {
            $this->setByModule($module);
        }
    }

    /**
     * Association setter method.
     *
     * @param string $type Association type
     * @param string $alias Association alias
     * @param array $options Association options
     *
     * @see \Cake\ORM\Table::belongsTo()
     * @see \Cake\ORM\Table::belongsToMany()
     * @see \Cake\ORM\Table::hasMany()
     * @see \Cake\ORM\Table::hasOne()
     *
     * @return void
     */
    protected function setAssociation($type, $alias, array $options)
    {
        $this->{$type}($alias, $options);
    }

    /**
     * Set associations from "module" type Modules.
     *
     * @param string $module Module name
     * @return void
     */
    private function setByModule($module)
    {
        $config = (new ModuleConfig(ConfigType::MODULE(), $module))->parse();
        $fields = $this->getModuleFields($module);

        if ('module' === $config->table->type) {
            $this->setByTypeModule($module, $fields);
        }

        if ('relation' === $config->table->type) {
            $this->setByTypeRelation($module, $fields);
        }

        if ($module === $this->getTableName()) {
            $this->setByTypeFile($module, $fields);
        }
    }

    /**
     * Set associations from "module" type Modules.
     *
     * @param string $module Module name
     * @param array $fields Module fields
     * @return void
     */
    private function setByTypeModule($module, array $fields)
    {
        foreach ($fields as $field) {
            $this->setByTypeModuleField($module, $field);
        }
    }

    /**
     * Set associations from "relation" type Modules.
     *
     * @param string $module Module name
     * @param array $fields Module fields
     * @return void
     */
    private function setByTypeRelation($module, array $fields)
    {
        $moduleField = $this->getModuleRelatedField($fields);

        if (is_null($moduleField)) {
            return;
        }

        foreach ($fields as $field) {
            $this->setByTypeRelationField($module, $field, $moduleField);
        }
    }

    /**
     * Set Burzum/FileStorage associations.
     *
     * @param string $module Module name
     * @param array $fields Module fields
     * @return void
     */
    private function setByTypeFile($module, array $fields)
    {
        foreach ($fields as $field) {
            $this->setByTypeFileField($field);
        }
    }

    /**
     * Set associations by field, for "module" type Modules.
     *
     * @param string $module Module name
     * @param \CsvMigrations\FieldHandlers\CsvField $field CSV Field instance
     * @return void
     */
    private function setByTypeModuleField($module, CsvField $field)
    {
        // skip non related type
        if (! $this->isRelatedType($field)) {
            return;
        }

        // skip associations between other modules
        if (! in_array($this->getTableName(), [$module, $field->getAssocCsvModule()])) {
            return;
        }

        /**
         * for current table instance "Articles", assuming that the provide $module is "Articles" and
         * a field "author_id" of type "related(Authors)" is found in its migration.json config,
         * then we set the association as follows:
         *
         * $articlesTable->belongTo('AuthorIdAuthors', ['className' => 'Authors', 'foreignKey' => 'author_id']);
         */
        if ($this->getTableName() === $module) {
            $className = $field->getAssocCsvModule();
            $associationType = 'belongsTo';
        }

        /**
         * for current table instance "Authors", assuming that the provide $module is "Articles" and
         * a field "related_author" of type "related(Authors)" is found in its migration.json config,
         * then we set the association as follows:
         *
         * $authorsTable->hasMany('RelatedAuthorArticles', ['className' => 'Articles', 'foreignKey' => 'related_author']);
         */
        if ($this->getTableName() === $field->getAssocCsvModule()) {
            $className = $module;
            $associationType = 'hasMany';
        }

        /**
         * for current table instance "Articles", assuming that the provide $module is "Articles" and
         * a field "main_article" of type "related(Articles)" is found in its migration.json config,
         * then we set the association as follows:
         *
         * $articlesTable->belongTo('MainArticleArticles', ['className' => 'Articles', 'foreignKey' => 'main_article']);
         */
        if ($field->getAssocCsvModule() === $module) {
            $className = $module;
            $associationType = 'belongsTo';
        }

        $this->setAssociation(
            $associationType,
            static::generateAssociationName($className, $field->getName()),
            ['className' => $className, 'foreignKey' => $field->getName()]
        );
    }

    /**
     * Set associations by field, for "relation" type Modules.
     *
     * @param string $module Module name
     * @param \CsvMigrations\FieldHandlers\CsvField $field CSV Field instance
     * @param \CsvMigrations\FieldHandlers\CsvField $moduleField Module related CSV Field instance
     * @return void
     */
    private function setByTypeRelationField($module, CsvField $field, CsvField $moduleField)
    {
        if (! $this->isRelatedType($field)) {
            return;
        }

        // skip for field with type "related(Articles)" when current module is "Articles"
        if ($this->getTableName() === $field->getAssocCsvModule()) {
            return;
        }

        // skip for fields associated with Footprint behavior ('related' type fields associated with Users table)
        if ($this->isFootprintField($field) || $this->isFootprintField($moduleField)) {
            return;
        }

        $this->setAssociation(
            'belongsToMany',
            static::generateAssociationName($module, $field->getName()),
            [
                'joinTable' => Inflector::tableize($module),
                'className' => $field->getAssocCsvModule(),
                'foreignKey' => $moduleField->getName(),
                'targetForeignKey' => $field->getName()
            ]
        );
    }

    /**
     * Validates whether the provided field is used in Footprint behavior.
     *
     * @param \CsvMigrations\FieldHandlers\CsvField $field CSV Field instance
     * @return bool
     */
    private function isFootprintField(CsvField $field)
    {
        if (! $this->hasBehavior('Footprint')) {
            return false;
        }

        return in_array($field->getName(), $this->behaviors()->get('Footprint')->getConfig());
    }

    /**
     * Set associations by file type field, for "module" type Modules.
     *
     * @param \CsvMigrations\FieldHandlers\CsvField $field CSV Field instance
     * @return void
     */
    private function setByTypeFileField(CsvField $field)
    {
        if (! in_array($field->getType(), ['files', 'images'])) {
            return;
        }

        $this->setAssociation(
            'hasMany',
            static::generateAssociationName(FileUpload::FILE_STORAGE_TABLE_NAME, $field->getName()),
            [
                'className' => FileUpload::FILE_STORAGE_TABLE_NAME,
                'foreignKey' => 'foreign_key',
                'conditions' => ['model' => $this->getTable(), 'model_field' => $field->getName()]
            ]
        );
    }

    /**
     * Current Table name getter.
     *
     * @return string
     */
    private function getTableName()
    {
        return App::shortName(get_class($this), 'Model/Table', 'Table');
    }

    /**
     * Retrieves current module related field, from "relation" type Modules.
     *
     * @param array $fields Module fields
     * @return \CsvMigrations\FieldHandlers\CsvField|null
     */
    private function getModuleRelatedField(array $fields)
    {
        foreach ($fields as $field) {
            if ($this->getTableName() === $field->getAssocCsvModule()) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Validates if specified field if of type "related"
     *
     * @param \CsvMigrations\FieldHandlers\CsvField $field CSV field instance
     * @return bool
     */
    private function isRelatedType(CsvField $field)
    {
        return 'related' === $field->getType();
    }

    /**
     * Retrieves specified Module fields.
     *
     * @param string $module Module name
     * @return array
     */
    private function getModuleFields($module)
    {
        $fields = (new ModuleConfig(ConfigType::MIGRATION(), $module))->parse();
        $fields = json_decode(json_encode($fields), true);

        foreach ($fields as $k => $v) {
            $fields[$k] = new CsvField($v);
        }

        return $fields;
    }

    /**
     * Generates unique association name based on Table name and foreign key.
     *
     *  For example, "Articles" migration.json includes a field "author_id,related(Authors)":
     *
     * AuthorIdArticles
     *
     * @param string $tableName Table name
     * @param string $foreignKey Foreign key
     * @return string
     */
    public static function generateAssociationName($tableName, $foreignKey)
    {
        list($plugin, $tableName) = pluginSplit($tableName);
        $plugin = false !== strpos($plugin, '/') ? substr($plugin, strpos($plugin, '/') + 1) : $plugin;

        return Inflector::camelize($foreignKey) . $plugin . $tableName;
    }
}
