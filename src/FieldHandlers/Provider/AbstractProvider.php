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
namespace CsvMigrations\FieldHandlers\Provider;

use CsvMigrations\FieldHandlers\Config\ConfigInterface;

/**
 * AbstractProvider
 *
 * AbstractProvider is the base class for all
 * providers, implementing ProviderInterface.
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var \CsvMigrations\FieldHandlers\Config\ConfigInterface $config Configuration
     */
    protected $config;

    /**
     * Constructor
     *
     * @param \CsvMigrations\FieldHandlers\Config\ConfigInterface $config Configuration
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Render element
     *
     * @param string $name Element name
     * @param array $options Options to pass to the element
     * @return string
     */
    protected function renderElement($name, array $options = [])
    {
        $view = $this->config->getView();
        $result = $view->element($name, $options);

        return $result;
    }
}
