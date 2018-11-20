<?php
namespace CsvMigrations\Aggregator;

use RuntimeException;

abstract class AbstractAggregator implements AggregatorInterface
{
    /**
     * Configuration instance.
     *
     * @var \CsvMigrations\Aggregator\Configuration
     */
    private $config;

    /**
     * Validation errors storage.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Constructor method.
     *
     * Mostly used for properties assignment and validation.
     *
     * @param \CsvMigrations\Aggregator\Configuration $config Aggregator configuration
     * @return void
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;

        if (! $this->validate()) {
            throw new RuntimeException(sprintf('Validation failed: %s', implode(', ', $this->errors)));
        }
    }

    /**
     * Validator method.
     *
     * Checks for column existance.
     *
     * @return bool
     */
    public function validate() : bool
    {
        /** @var \Cake\Datasource\RepositoryInterface&\Cake\ORM\Table */
        $table = $this->getConfig()->getTable();

        foreach ([$this->config->getField(), $this->config->getDisplayField()] as $field) {
            if ($table->getSchema()->hasColumn($field)) {
                continue;
            }

            $this->errors[] = sprintf('Unknown column "%s" for table "%s"', $field, $table->getAlias());
        }

        return empty($this->errors);
    }

    /**
     * Configuration instance getter.
     *
     * @return \CsvMigrations\Aggregator\Configuration
     */
    final public function getConfig() : Configuration
    {
        return $this->config;
    }
}
