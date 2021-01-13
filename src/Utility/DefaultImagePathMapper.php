<?php
declare(strict_types=1);

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

namespace CsvMigrations\Utility;

use Burzum\FileStorage\Model\Entity\FileStorage;
use Cake\Event\Event;
use Cake\Event\EventManager;

class DefaultImagePathMapper implements ImagePathMapper
{
    /**
     * Image URL getter.
     *
     * @param \Burzum\FileStorage\Model\Entity\FileStorage $entity FileStorage entity
     * @param string $version Version name
     * @return ?string
     */
    public function getImagePath(FileStorage $entity, string $version): ?string
    {
        $event = new Event('ImageVersion.getVersions', $this, [
            'image' => $entity,
            'version' => $version,
            'options' => [],
            'pathType' => 'fullPath',
        ]);

        try {
            EventManager::instance()->dispatch($event);
        } catch (\RuntimeException $e) {
            // In case of invalid size
            return null;
        }

        $result = $event->getResult();
        if (!$result) {
            return null;
        }

        $path = $entity->get('path');
        $fullPath = WWW_ROOT . DS . $result;
        if (!file_exists($fullPath)) {
            return null;
        }

        return $result;
    }
}
