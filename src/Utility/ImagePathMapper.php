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

/**
 * Interface to allow applications to override image urls
 */
interface ImagePathMapper
{
    /**
     * Image URL getter.
     *
     * @param \Burzum\FileStorage\Model\Entity\FileStorage $entity FileStorage entity
     * @param string $version Version name
     * @return ?string
     */
    public function getImagePath(FileStorage $entity, string $version): ?string;
}
