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
namespace CsvMigrations\FieldHandlers\Provider\Config;

use InvalidArgumentException;

/**
 * Config
 *
 * This class provides the functionality of the
 * field handler configuration.
 */
class Config implements ConfigInterface
{
    /**
     * @var array $config Field handler configuration
     */
    protected $config = [];

    /**
     * @var array $validateRules Validation rules
     */
    protected $validateRules = [
        'searchOperators' => [
            'required' => true,
            'type' => 'string',
            'implements' => 'CsvMigrations\\FieldHandlers\\Provider\\SearchOperators\\SearchOperatorsInterface',
        ],
        'valueRenderAs' => [
            'required' => true,
            'type' => 'string',
            'implements' => 'CsvMigrations\\FieldHandlers\\Renderer\\Value\\RendererInterface',
        ],
        'nameRenderAs' => [
            'required' => true,
            'type' => 'string',
            'implements' => 'CsvMigrations\\FieldHandlers\\Renderer\\Name\\RendererInterface',
        ],

    ];

    /**
     * Set config
     *
     * @throws \InvalidArgumentException for invalid configuration
     * @param array $config Field Handler configuration
     * @return void
     */
    public function setConfig(array $config)
    {
        $this->validateConfig($config);
        $this->config = $config;
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig()
    {
        $this->validateConfig($this->config);

        return $this->config;
    }

    /**
     * Validate config
     *
     * @throws \InvalidArgumentException for invalid configuration
     * @param array $config Field Handler configuration
     * @return void
     */
    public function validateConfig(array $config)
    {
        foreach ($this->validateRules as $option => $params) {
            if ($params['required'] && empty($config[$option])) {
                throw new InvalidArgumentException("Required configuration option [$option] is missing");
            }

            $type = gettype($config[$option]);
            if ($type <> $params['type']) {
                throw new InvalidArgumentException("Configuration option [$option] is of wrong type [$type].  Expecting [" . $params['type'] . "]");
            }

            switch ($type) {
                case 'string':
                    if (!class_exists($config[$option])) {
                        throw new InvalidArgumentException("Configuration option [$option] refers to non-existing class [" . $config[$option] . "]");
                    }
                    $requiredInterface = !empty($params['implements']) ? $params['implements'] : '';
                    if ($requiredInterface && !in_array($requiredInterface, class_implements($config[$option]))) {
                        throw new InvalidArgumentException("Configuration option [$option] class [" . $config[$option] . "] does not implement required interface [$requiredInterface]");
                    }
                    break;
            }
        }
    }
}
