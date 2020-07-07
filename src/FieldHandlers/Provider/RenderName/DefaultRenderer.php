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

namespace CsvMigrations\FieldHandlers\Provider\RenderName;

use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\Provider\AbstractProvider;

/**
 * DefaultRenderer
 *
 * This class provides the default renderer
 * for the field name.
 */
class DefaultRenderer extends AbstractProvider
{
    /**
     * Provide rendered name
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        // Return as is, if not empty
        if (!empty($data)) {
            return $data;
        }

        // Fallback on the field name
        $result = $this->config->getField();

        $result = $this->cleanName($result);

        return $result;
    }

    /**
     * Clean (field) name
     *
     * @see FormHelper::label()
     * @param string $name Field name
     * @return string
     */
    protected function cleanName(string $name): string
    {
        if ('._ids' === substr($name, -5)) {
            $name = substr($name, 0, -5);
        }

        if (false !== strpos($name, '.')) {
            $fieldElements = explode('.', $name);
            $name = false !== end($fieldElements) ? end($fieldElements) : $name;
        }

        if (substr($name, -3) === '_id') {
            $name = substr($name, 0, -3);
        }
        $name = Inflector::humanize(Inflector::underscore($name));

        return $name;
    }
}
