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

namespace CsvMigrations\Event;

use Burzum\FileStorage\Model\Entity\FileStorage;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Table;
use CsvMigrations\Utility\FileUpload;
use Webmozart\Assert\Assert;

class ProcessThumbnailsListener implements EventListenerInterface
{
    /**
     * Implemented Events
     * @return array
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::CREATE_THUMBNAILS() => 'createThumbnails',
            (string)EventName::REMOVE_THUMBNAILS() => 'removeThumbnails',
        ];
    }

    /**
     * Generate thumbnails
     *
     * @param \Cake\Event\Event $event Cake event.
     * @param \Burzum\FileStorage\Model\Entity\FileStorage $entity File storage entity.
     * @return bool
     */
    public function createThumbnails(Event $event, FileStorage $entity): bool
    {
        $table = $event->getSubject();
        Assert::isInstanceOf($table, Table::class);

        $fileUpload = new FileUpload($table);

        return $fileUpload->createThumbnails($entity);
    }

    /**
     * Remove thumbnails
     *
     * @param \Cake\Event\Event $event Cake event.
     * @param \Burzum\FileStorage\Model\Entity\FileStorage $entity File storage entity.
     * @return bool
     */
    public function removeThumbnails(Event $event, FileStorage $entity): bool
    {
        $table = $event->getSubject();
        Assert::isInstanceOf($table, Table::class);

        $fileUpload = new FileUpload($table);

        return $fileUpload->removeThumbnails($entity);
    }
}
