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
namespace CsvMigrations\FieldHandlers\Config;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\View\View;
use CsvMigrations\View\AppView;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

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
     * @var \Cake\ORM\Table $table Table intance
     */
    protected $table;

    /**
     * @var array $options Options
     */
    protected $options;

    /**
     * @var \Cake\View\View $view View
     */
    protected $view;

    /**
     * @var array $providers List of provider names and classes
     */
    protected $providers = [];

    /**
     * @var array $requiredProviders List of required providers
     */
    protected $requiredProviders = [
        'applicationRules',
        'combinedFields',
        'dbFieldType',
        'fieldValue',
        'fieldToDb',
        'searchOperators',
        'searchOptions',
        'selectOptions',
        'renderInput',
        'renderValue',
        'renderName',
        'validationRules'
    ];

    /**
     * Constructor
     *
     * @param string $field Field name
     * @param \Cake\Datasource\RepositoryInterface|string $table Table name or instance
     * @param array $options Options
     */
    public function __construct(string $field, $table = '', array $options = [])
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
    public function setField(string $field) : void
    {
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
    public function getField() : string
    {
        return $this->field;
    }

    /**
     * Set table
     *
     * @param \Cake\Datasource\RepositoryInterface|string $table Table name or instance
     * @return void
     */
    public function setTable($table = '') : void
    {
        if ('' === $table) {
            $table = 'dummy';
        }

        if (is_string($table)) {
            $table = TableRegistry::get($table);
        }

        Assert::isInstanceOf($table, Table::class);

        $this->table = $table;
    }

    /**
     * Get table
     *
     * @return \Cake\ORM\Table
     */
    public function getTable() : Table
    {
        return $this->table;
    }

    /**
     * Set options
     *
     * @param array $options Options
     * @return void
     */
    public function setOptions(array $options = []) : void
    {
        $this->options = $options;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * Set view
     *
     * @param \Cake\View\View $view View instance
     * @return void
     */
    public function setView(View $view) : void
    {
        $this->view = $view;
    }

    /**
     * Get view
     *
     * @return \Cake\View\View
     */
    public function getView() : View
    {
        if (empty($this->view)) {
            $this->setView(new AppView());
        }

        return $this->view;
    }

    /**
     * Set providers
     *
     * @throws \InvalidArgumentException for invalid providers
     * @param array $providers List of provider names and classes
     * @return void
     */
    public function setProviders(array $providers) : void
    {
        $this->validateProviders($providers);
        $this->providers = $providers;
    }

    /**
     * Get providers
     *
     * @throws \InvalidArgumentException for invalid provider
     * @return array
     */
    public function getProviders() : array
    {
        $this->validateProviders($this->providers);

        return $this->providers;
    }

    /**
     * Get provider by name
     *
     * @throws \InvalidArgumentException for invalid provider
     * @param string $name Name of the provider to get
     * @return string
     */
    public function getProvider(string $name) : string
    {
        $providers = $this->getProviders();
        if (!in_array($name, array_keys($providers))) {
            throw new InvalidArgumentException("Provider for [$name] is not configured");
        }

        return $providers[$name];
    }

    /**
     * Validate providers
     *
     * @throws \InvalidArgumentException for invalid providers
     * @param array $providers List of provider names and classes
     * @return void
     */
    public function validateProviders(array $providers) : void
    {
        Assert::allClassExists($providers);
        Assert::allImplementsInterface($providers, self::PROVIDER_INTERFACE);

        foreach ($this->requiredProviders as $name) {
            if (!in_array($name, array_keys($providers))) {
                throw new InvalidArgumentException("Configuration is missing a required provider for [$name]");
            }
        }
    }
}
