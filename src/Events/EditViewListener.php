<?php
namespace CsvMigrations\Events;

use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use CsvMigrations\Events\BaseViewListener;

class EditViewListener extends BaseViewListener
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            'CsvMigrations.Edit.beforeFind' => 'beforeFind',
            'CsvMigrations.Edit.afterFind' => 'afterFind',
            'CsvMigrations.Edit.beforeSave' => 'beforeSave',
            'CsvMigrations.Edit.afterSave' => 'afterSave'
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
