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
 * ProviderInterface
 *
 * ProviderInterface defines the contract
 * for all providers, except Config providers
 */
interface ProviderInterface
{
    /**
     * Constructor
     *
     * @param \CsvMigrations\FieldHandlers\Config\ConfigInterface $config Configuration
     */
    public function __construct(ConfigInterface $config);

    /**
     * Provide
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = []);
}
