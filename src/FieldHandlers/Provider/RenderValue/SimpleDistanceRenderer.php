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

namespace CsvMigrations\FieldHandlers\Provider\RenderValue;

use Cake\Core\Configure;

/**
 * SimpleUnitRenderer
 *
 * Decimal renderer provides decimal rendering functionality.
 */
class SimpleDistanceRenderer extends NumberRenderer
{
    /**
     * Decimal precision
     */
    public const PRECISION = 2;

    /**
     * Provide rendered value
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $data = parent::provide($data, $options);

        if (empty($data)) {
            return '';
        }

        return preg_replace('/.00$/', '', $data) . ' ' . Configure::read('CsvMigrations.Inputmask.Distance.suffix', '');
    }
}
