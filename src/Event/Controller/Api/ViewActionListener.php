<?php
namespace CsvMigrations\Event\Controller\Api;

use Cake\Event\Event;
use Cake\Network\Request;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;
use CsvMigrations\Event\EventName;

class ViewActionListener extends BaseActionListener
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::API_VIEW_BEFORE_FIND() => 'beforeFind',
            (string)EventName::API_VIEW_AFTER_FIND() => 'afterFind'
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
        $table = $event->subject()->{$event->subject()->name};
        $request = $event->subject()->request;

        $this->_resourceToString($entity);

        if (static::FORMAT_PRETTY === $request->query('format')) {
            $this->_prettify($entity, $table, []);
        } else { // @todo temporary functionality, please see _includeFiles() method documentation.
            $this->_restructureFiles($entity, $table);
        }

        $displayField = $table->displayField();
        $entity->{$displayField} = $entity->get($displayField);
    }
}
