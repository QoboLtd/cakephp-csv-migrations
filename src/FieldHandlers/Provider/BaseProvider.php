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

use CsvMigrations\FieldHandlers\Provider\Config\ConfigInterface;

/**
 * BaseProvider
 *
 * BaseProvider is the base class for all
 * providers, implementing ProviderInterface.
 */
abstract class BaseProvider implements ProviderInterface
{
    /**
     * @var ConfigInterface $config Configuration
     */
    protected $config;

    /**
     * Constructor
     *
     * @param ConfigInterface $config Configuration
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }
}
