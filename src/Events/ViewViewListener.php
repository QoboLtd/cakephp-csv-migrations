<?php
namespace CsvMigrations\Events;

use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use CsvMigrations\Events\BaseViewListener;

class ViewViewListener extends BaseViewListener
{
    /**
     * Pretty format identifier
     */
    const FORMAT_PRETTY = 'pretty';

    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            'CsvMigrations.View.beforeFind' => 'beforeFind',
            'CsvMigrations.View.afterFind' => 'afterFind'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFind(Event $event, Query $query)
    {
        $this->_lookupFields($query, $event);
        $query->contain($this->_getAssociations($event));
    }

    /**
     * {@inheritDoc}
     */
    public function afterFind(Event $event, Entity $entity)
    {
        $this->_resourceToString($entity);
        // @todo temporary functionality, please see _includeFiles() method documentation.
        $this->_includeFiles($entity, $event);

        $this->_prettifyEntity($entity, $event);
    }

    /**
     * Method that prepares entity to run through pretiffy logic.
     *
     * @param  \Cake\ORM\Entity  $entity Entity
     * @param  \Cake\Event\Event $event  Event instance
     * @return void
     */
    protected function _prettifyEntity(Entity $entity, Event $event)
    {
        if (!in_array($event->subject()->request->query('format'), [static::FORMAT_PRETTY])) {
            return;
        }

        $this->_prettify($entity, $event->subject()->{$event->subject()->name}, []);
    }
}
