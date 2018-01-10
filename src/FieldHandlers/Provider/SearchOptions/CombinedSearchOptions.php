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
namespace CsvMigrations\FieldHandlers\Provider\SearchOptions;

/**
 * CombinedSearchOptions
 *
 * Combined search options
 */
class CombinedSearchOptions extends AbstractSearchOptions
{
    /**
     * Provide search options
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $result = [];

        $combinedFields = $this->config->getProvider('combinedFields');
        $combinedFields = new $combinedFields($this->config);
        $combinedFields = $combinedFields->provide($data, $options);

        $view = $this->config->getView();
        foreach ($combinedFields as $suffix => $fieldOptions) {
            $options['fieldDefinitions']->setType($fieldOptions['handler']::DB_FIELD_TYPE);
            $fieldName = $this->config->getField() . '_' . $suffix;
            $handler = new $fieldOptions['handler']($this->config->getTable(), $fieldName, $view);
            $fieldOptions = $handler->getSearchOptions($options);
            if (!empty($fieldOptions)) {
                $result = array_merge($result, $fieldOptions);
            }
        }

        return $result;
    }
}
