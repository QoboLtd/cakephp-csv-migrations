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
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\CsvField;
use InvalidArgumentException;

class DbField
{
    /**
     * field name
     *
     * @var string
     */
    protected $name;

    /**
     * field type
     *
     * @var string
     */
    protected $type;

    /**
     * field non-searchable flag
     *
     * @var bool
     */
    protected $nonSearchable;

    /**
     * field unique flag
     *
     * @var bool
     */
    protected $unique;

    /**
     * Column options
     *
     * Initially populated from the default options,
     * based on the column type.
     *
     * @var array
     */
    protected $options;

    /**
     * Supported field types and their default options
     *
     * @var array
     */
    protected $defaultOptions = [
        'uuid' => [],
        'string' => ['limit' => 255],
        'integer' => [],
        'decimal' => ['precision' => 10, 'scale' => 2],
        'boolean' => [],
        'text' => [],
        'blob' => [],
        'datetime' => [],
        'date' => [],
        'time' => [],
    ];

    /**
     * Constructor
     *
     * @param string $name Field name
     * @param string $type Field type
     * @param int|string|null $limit Field limit
     * @param bool $required Field required flag
     * @param bool $nonSearchable Field non-searchable flag
     * @param bool $unique Field unique flag
     */
    public function __construct(string $name, string $type, $limit, bool $required, bool $nonSearchable, bool $unique)
    {
        $this->setName($name);
        $this->setType($type);
        $this->setDefaultOptions();

        $this->setLimit($limit);
        $this->setRequired($required);
        $this->setNonSearchable($nonSearchable);
        $this->setUnique($unique);
    }

    /**
     * Construct a new instance from CsvField
     *
     * @param \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return \CsvMigrations\FieldHandlers\DbField
     */
    public static function fromCsvField(CsvField $csvField) : DbField
    {
        return new self(
            $csvField->getName(),
            $csvField->getType(),
            $csvField->getLimit(),
            $csvField->getRequired(),
            $csvField->getNonSearchable(),
            $csvField->getUnique()
        );
    }

    /**
     * Populate options with defaults
     *
     * @return void
     */
    protected function setDefaultOptions() : void
    {
        $type = $this->getType();
        if (! empty($this->defaultOptions[$type])) {
            $this->options = $this->defaultOptions[$type];
        }
    }

    /**
     * Set options.
     *
     * @param mixed[] $options Options to set
     * @return void
     */
    public function setOptions(array $options = []) : void
    {
        $this->options = $options;
    }

    /**
     * Get options.
     *
     * @return mixed[]
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * Field name setter.
     *
     * @param string $name field name
     * @return void
     */
    protected function setName(string $name) : void
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Empty field name is not allowed');
        }

        $this->name = $name;
    }

    /**
     * Field name getter.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Field type setter.
     *
     * @param string $type field type
     * @return void
     */
    protected function setType(string $type) : void
    {
        if (empty($type)) {
            throw new InvalidArgumentException(__CLASS__ . ': Empty field type is not allowed');
        }

        if (! in_array($type, array_keys($this->defaultOptions))) {
            throw new InvalidArgumentException(__CLASS__ . ': Unsupported field type: ' . $type);
        }

        $this->type = $type;
    }

    /**
     * Field type getter.
     *
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Field limit setter.
     *
     * @param int|string|null $limit field limit
     * @return void
     */
    public function setLimit($limit = null) : void
    {
        if ($limit !== null) {
            $this->options['limit'] = $limit;
        }
    }

    /**
     * Field limit getter.
     *
     * @return int|string|null
     */
    public function getLimit()
    {
        $result = null;
        if (isset($this->options['limit'])) {
            $result = $this->options['limit'];
        }

        return $result;
    }

    /**
     * Field required flag setter.
     *
     * @param bool $required field required flag
     * @return void
     */
    public function setRequired(bool $required) : void
    {
        // flip $required into allow null flag
        $this->options['null'] = ! $required;
    }

    /**
     * Field required flag getter.
     *
     * @return bool
     */
    public function getRequired() : bool
    {
        return isset($this->options['null']) ?
            ! $this->options['null'] : // flip allow null flag into $required
            false;
    }

    /**
     * Field non-searchable flag setter.
     *
     * @param bool $nonSearchable field non-searchable flag
     * @return void
     */
    public function setNonSearchable(bool $nonSearchable) : void
    {
        $this->nonSearchable = $nonSearchable;
    }

    /**
     * Field non-searchable flag getter.
     *
     * @return bool
     */
    public function getNonSearchable() : bool
    {
        return $this->nonSearchable;
    }

    /**
     * Field unique flag setter.
     *
     * @param bool $unique field unique flag
     * @return void
     */
    public function setUnique(bool $unique) : void
    {
        $this->unique = $unique;
    }

    /**
     * Field unique flag getter.
     *
     * @return bool
     */
    public function getUnique() : bool
    {
        return $this->unique;
    }
}
