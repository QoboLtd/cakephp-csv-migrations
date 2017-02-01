<?php
namespace CsvMigrations\Events;

use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use CsvMigrations\Events\BaseViewListener;

class ViewViewListener extends BaseViewListener
{
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
        $table = $event->subject()->{$event->subject()->name};
        $request = $event->subject()->request;

        $this->_lookupFields($query, $event);

        if (static::FORMAT_PRETTY !== $request->query('format')) {
            $query->contain($this->_getFileAssociations($table));
        }
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

        $displayField = $event->subject()->{$event->subject()->name}->displayField();
        $entity->{$displayField} = $entity->get($displayField);
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
