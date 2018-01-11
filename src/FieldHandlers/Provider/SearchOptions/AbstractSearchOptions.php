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

use CsvMigrations\FieldHandlers\Provider\AbstractProvider;

/**
 * AbstractSearchOptions
 *
 * Abstract base class extending AbstractProvider
 */
abstract class AbstractSearchOptions extends AbstractProvider
{
    /**
     * Helper method to get search operators
     *
     * @return array
     */
    protected function getSearchOperators()
    {
        $result = $this->config->getProvider('searchOperators');
        $result = new $result($this->config);
        $result = $result->provide();

        return $result;
    }

    /**
     * Get default search options
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return array
     */
    protected function getDefaultOptions($data = null, array $options = [])
    {
        $result = [
            'type' => $options['fieldDefinitions']->getType(),
            'label' => $options['label'],
            'operators' => $this->getSearchOperators(),
            'input' => [
                'content' => '',
            ],
        ];

        return $result;
    }

    /**
     * Get basic template for a given type
     *
     * @param string $type Form input type
     * @return string
     */
    protected function getBasicTemplate($type)
    {
        $view = $this->config->getView();
        $result = $view->Form->input('{{name}}', [
            'value' => '{{value}}',
            'type' => $type,
            'label' => false
        ]);

        return $result;
    }
}
