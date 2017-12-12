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
namespace CsvMigrations\FieldHandlers\Renderer\Name;

use Cake\Utility\Inflector;

/**
 * DefaultRenderer
 *
 * This class provides the default renderer
 * for the field name.
 */
class DefaultRenderer implements RendererInterface
{
    /**
     * Render name
     *
     * @param string $name Name to render
     * @param array $options Rendering options
     * @return string Text, HTML or other string result
     */
    public function render($name, array $options = [])
    {
        $result = '';

        // Return as is, if not empty
        if (!empty($name)) {
            return $name;
        }

        // Fallback on the default
        if (!empty($options['default'])) {
            $result = $options['default'];
        }

        // Return empty string unless default provided
        if (empty($result)) {
            return $result;
        }

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
    protected function cleanName($name)
    {
        if (substr($name, -5) === '._ids') {
            $name = substr($name, 0, -5);
        }
        if (strpos($name, '.') !== false) {
            $fieldElements = explode('.', $name);
            $name = array_pop($fieldElements);
        }
        if (substr($name, -3) === '_id') {
            $name = substr($name, 0, -3);
        }
        $name = __(Inflector::humanize(Inflector::underscore($name)));

        return $name;
    }
}
