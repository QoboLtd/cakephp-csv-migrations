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

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use CsvMigrations\View\AppView;
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
     * Interface for provider classes
     */
    const PROVIDER_INTERFACE = 'CsvMigrations\\FieldHandlers\\Provider\\ProviderInterface';

    /**
     * @var string $field Field name
     */
    protected $field;

    /**
     * @var object $table Table intance
     */
    protected $table;

    /**
     * @var array $options Options
     */
    protected $options;

    /**
     * @var array $config Field handler configuration
     */
    protected $config = [];

    /**
     * @var array $requiredProviders List of required providers
     */
    protected $requiredProviders = [
        'fieldValue',
        'fieldToDb',
        'searchOperators',
        'searchOptions',
        'selectOptions',
        'inputRenderAs',
        'valueRenderAs',
        'nameRenderAs',
    ];

    /**
     * Constructor
     *
     * @param string $field Field name
     * @param mixed $table Table name or instance
     * @param array $options Options
     */
    public function __construct($field, $table = null, array $options = [])
    {
        $this->setField($field);
        $this->setTable($table);
        $this->setOptions($options);
    }

    /**
     * Set field
     *
     * @throws \InvalidArgumentException when field is empty or not a string
     * @param string $field Field name
     * @return void
     */
    public function setField($field)
    {
        if (!is_string($field)) {
            throw new InvalidArgumentException("Field is not a string");
        }

        $field = trim($field);
        if (empty($field)) {
            throw new InvalidArgumentException("Field is empty");
        }

        $this->field = $field;
    }

    /**
     * Get field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set table
     *
     * @param mixed $table Table name or instance
     * @return void
     */
    public function setTable($table = null)
    {
        if (empty($table)) {
            $table = 'dummy';
        }

        if (is_string($table)) {
            $table = TableRegistry::get($table);
        }

        if (!$table instanceof Table) {
            throw new InvalidArgumentException("Given table is not an instance of ORM Table");
        }

        $this->table = $table;
    }

    /**
     * Get table
     *
     * @return object
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set options
     *
     * @param array $options Options
     * @return void
     */
    public function setOptions(array $options = [])
    {
        if (empty($options['view'])) {
            $options['view'] = new AppView();
        }

        $this->options = $options;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

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
        foreach ($config as $name => $class) {
            if (!is_string($class)) {
                throw new InvalidArgumentException("Provider class for [$name] is not a string");
            }

            $class = trim($class);
            if (empty($class)) {
                throw new InvalidArgumentException("Provider class for [$name] is an empty string");
            }

            if (!class_exists($class)) {
                throw new InvalidArgumentException("Provider class [$class] for [$name] does not exist");
            }

            if (!in_array(self::PROVIDER_INTERFACE, class_implements($class))) {
                throw new InvalidArgumentException("Provider class [$class] for [$name] does not implement [" . self::PROVIDER_INTERFACE . "] interface");
            }
        }

        foreach ($this->requiredProviders as $name) {
            if (!in_array($name, array_keys($config))) {
                throw new InvalidArgumentException("Configuration is missing a required provider for [$name]");
            }
        }
    }
}
