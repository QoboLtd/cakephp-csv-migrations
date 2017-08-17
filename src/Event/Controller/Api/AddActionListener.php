<?php
namespace CsvMigrations\Event\Controller\Api;

use Cake\Event\Event;
use Cake\ORM\Entity;
use CsvMigrations\Event\EventName;

class AddActionListener extends BaseActionListener
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::API_ADD_BEFORE_SAVE() => 'beforeSave',
            (string)EventName::API_ADD_AFTER_SAVE() => 'afterSave'
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
