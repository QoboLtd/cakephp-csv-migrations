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
namespace CsvMigrations\FieldHandlers\Provider\RenderInput;

use Cake\Utility\Inflector;
use CsvMigrations\FileUploadsUtils;

/**
 * FilesdRenderer
 *
 * Files renderer provides the functionality
 * for rendering files inputs.
 */
class FilesRenderer extends AbstractRenderer
{
    /**
     * Provide
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $field = $this->config->getField();
        $table = $this->config->getTable();

        $fieldName = $table->aliasField($field);

        $entities = null;
        if (!empty($data)) {
            $fileUploadsUtils = new FileUploadsUtils($table);
            $entities = $fileUploadsUtils->getFiles($table, $field, $data);
        }

        $params = [
            'field' => $field,
            'name' => $fieldName,
            'type' => 'file',
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'entities' => $entities,
            'table' => Inflector::dasherize($table->alias()),
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/FilesFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);
    }
}
