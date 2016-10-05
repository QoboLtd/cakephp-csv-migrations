<?php
namespace CsvMigrations;

use Cake\ORM\Entity;
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
     * @param  Cake\ORM\Entity     $entity    Entity instance
     * @param  string              $tableName Table name
     * @param  array               $fields    Fields to prettify
     * @return Cake\ORM\Entity
     */
    protected function _prettify(Entity $entity, $tableName, array $fields = [])
    {
        if (!$this->__fhf instanceof FieldHandlerFactory) {
            $this->__fhf = new FieldHandlerFactory();
        }
        if (empty($fields)) {
            $fields = array_keys($entity->toArray());
        }

        foreach ($fields as $field) {
            // skip fields that are not set in the current entity
            if (!isset($entity->{$field})) {
                continue;
            }

            $renderOptions = ['entity' => $entity];
            $entity->{$field} = $this->__fhf->renderValue(
                $tableName,
                $field,
                $entity->{$field},
                $renderOptions
            );
        }

        return $entity;
    }
}
