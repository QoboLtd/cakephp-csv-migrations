<?php
namespace CsvMigrations\Event\Controller\Api;

use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use CsvMigrations\Event\EventName;

class EditActionListener extends BaseActionListener
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            EventName::API_EDIT_BEFORE_FIND()->getValue() => 'beforeFind',
            EventName::API_EDIT_AFTER_FIND()->getValue() => 'afterFind',
            EventName::API_EDIT_BEFORE_SAVE()->getValue() => 'beforeSave',
            EventName::API_EDIT_AFTER_SAVE()->getValue() => 'afterSave'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFind(Event $event, Query $query)
    {
        $this->_lookupFields($query, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function afterFind(Event $event, Entity $entity)
    {
        //
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
