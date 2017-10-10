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
            (string)EventName::API_EDIT_BEFORE_FIND() => 'beforeFind',
            (string)EventName::API_EDIT_AFTER_FIND() => 'afterFind',
            (string)EventName::API_EDIT_BEFORE_SAVE() => 'beforeSave',
            (string)EventName::API_EDIT_AFTER_SAVE() => 'afterSave'
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
