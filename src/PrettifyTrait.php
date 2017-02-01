<?php
namespace CsvMigrations;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

trait PrettifyTrait
{
    /**
     * An instance of Field Handler Factory
     * @var CsvMigrations\FieldHandlers\FieldHandlerFactory
     */
    private $__fhf;

    private $__tableName = null;

    /**
     * Method that renders Entity values through Field Handler Factory.
     *
     * @param  Cake\ORM\Entity       $entity    Entity instance
     * @param  Cake\ORM\Table|string $table     Table instance
     * @param  array                 $fields    Fields to prettify
     * @return void
     */
    protected function _prettify(Entity $entity, $table, array $fields = [])
    {
        if (!$this->__fhf instanceof FieldHandlerFactory) {
            $this->__fhf = new FieldHandlerFactory();
        }
        if (empty($fields)) {
            $fields = array_keys($entity->toArray());
        }

        foreach ($fields as $field) {
            // handle belongsTo associated data
            if ($entity->{$field} instanceof Entity) {
                $tableName = $table->association($entity->{$field}->source())->className();
                $this->_prettify($entity->{$field}, $tableName);
            }

            // handle hasMany associated data
            if (is_array($entity->{$field})) {
                if (empty($entity->{$field})) {
                    continue;
                }
                foreach ($entity->{$field} as $associatedEntity) {
                    if (!$associatedEntity instanceof Entity) {
                        continue;
                    }

                    list(, $associationName) = pluginSplit($associatedEntity->source());
                    $tableName = $table->association($associationName)->className();
                    $this->_prettify($associatedEntity, $tableName);
                }
            }


            $renderOptions = ['entity' => $entity];
            $entity->{$field} = $this->__fhf->renderValue(
                $table instanceof Table ? $table->registryAlias() : $table,
                $field,
                $entity->{$field},
                $renderOptions
            );
        }
    }
}
