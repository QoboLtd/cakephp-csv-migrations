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

use CsvMigrations\Utility\FileUpload;

/**
 * FilesRenderer
 *
 * Files value as a linkable URL with icon
 */
class FilesRenderer extends AbstractRenderer
{
    /**
     * Provide rendered value
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return string
     */
    public function provide($data = null, array $options = [])
    {
        if (! is_string($data)) {
            return '';
        }

        if ('' === $data) {
            return '';
        }

        /** @var \Cake\ORM\Table&\Cake\Datasource\RepositoryInterface */
        $table = $this->config->getTable();
        $field = $this->config->getField();

        $fileUpload = new FileUpload($table);

        $entities = $fileUpload->getFiles($field, $data);

        if ($entities->isEmpty()) {
            return '';
        }

        $defaultElement = 'CsvMigrations.FieldHandlers/FilesFieldHandler/value';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, ['entities' => $entities]);
    }
}
