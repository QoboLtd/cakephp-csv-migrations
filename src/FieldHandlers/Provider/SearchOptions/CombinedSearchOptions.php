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

use Cake\Utility\Inflector;

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

        foreach ($combinedFields as $suffix => $fieldOptions) {
            $fieldName = $this->config->getField() . '_' . $suffix;

            $config = new $fieldOptions['config']($fieldName, $this->config->getTable());

            $provider = $config->getProvider('renderName');
            $provider = new $provider($config);
            $options['label'] = $provider->provide();

            $provider = $config->getProvider('searchOptions');
            $provider = new $provider($config);

            $type = Inflector::underscore(str_replace('Config', '', (new \ReflectionClass($config))->getShortName()));
            $options['fieldDefinitions']->setName($fieldName);
            $options['fieldDefinitions']->setType($type);

            $result = array_merge($result, (array)$provider->provide($data, $options));
        }

        return $result;
    }
}
