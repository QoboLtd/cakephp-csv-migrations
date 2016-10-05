<?php
namespace CsvMigrations\Events;

use Cake\Event\Event;
use Cake\ORM\Entity;
use CsvMigrations\Events\BaseViewListener;


class AddViewListener extends BaseViewListener
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            'CsvMigrations.Add.beforeSave' => 'beforeSave',
            'CsvMigrations.Add.afterSave' => 'afterSave'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave(Event $event, Entity $entity)
    {
        $this->_associatedByLookupFields($entity, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave(Event $event, Entity $entity)
    {
        //
    }
}
