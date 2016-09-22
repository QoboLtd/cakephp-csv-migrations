<?php
namespace CsvMigrations\FieldHandlers;

use InvalidArgumentException;

class DbField
{
    /**
     * field name
     *
     * @var string
     */
    protected $_name;

    /**
     * field type
     *
     * @var string
     */
    protected $_type;

    /**
     * field non-searchable flag
     *
     * @var bool
     */
    protected $_nonSearchable;

    /**
     * field unique flag
     *
     * @var bool
     */
    protected $_unique;

    /**
     * Column options
     *
     * Initially populated from the default options,
     * based on the column type.
     *
     * @var array
     */
    protected $_options;

    /**
     * Supported field types and their default options
     *
     * @var array
     */
    protected $_defaultOptions = [
        'uuid' => [],
        'string' => ['limit' => 255],
        'integer' => [],
        'decimal' => ['scale' => 8, 'precision' => 2],
        'boolean' => [],
        'text' => [],
        'datetime' => [],
        'date' => [],
        'time' => [],
    ];

    /**
     * Constructor
     *
     * @param string $name          field name
     * @param string $type          field type
     * @param int    $limit         field limit
     * @param bool   $required      field required flag
     * @param bool   $nonSearchable field non-searchable flag
     * @param bool   $unique        field unique flag
     */
    public function __construct($name, $type, $limit, $required, $nonSearchable, $unique)
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
     * Populate options with defaults
     *
     * @return void
     */
    protected function setDefaultOptions()
    {
        $type = $this->getType();
        if (!empty($this->_defaultOptions[$type])) {
            $this->_options = $this->_defaultOptions[$type];
        }
    }

    /**
     * Set options
     *
     * @param array $options Options to set
     * @return void
     */
    public function setOptions(array $options = [])
    {
        $this->_options = $options;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Field name setter.
     *
     * @param string $name field name
     * @return void
     */
    protected function setName($name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Empty field name is not allowed');
        }

        $this->_name = $name;
    }

    /**
     * Field name getter.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Field type setter.
     *
     * @param string $type field type
     * @return void
     */
    protected function setType($type)
    {
        if (empty($type)) {
            throw new InvalidArgumentException(__CLASS__ . ': Empty field type is not allowed');
        }

        if (!in_array($type, array_keys($this->_defaultOptions))) {
            throw new InvalidArgumentException(__CLASS__ . ': Unsupported field type: ' . $type);
        }

        $this->_type = $type;
    }

    /**
     * Field type getter.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Field limit setter.
     *
     * @param int $limit field limit
     * @return void
     */
    public function setLimit($limit = null)
    {
        if ($limit !== null) {
            $this->_options['limit'] = $limit;
        }
    }

    /**
     * Field limit getter.
     *
     * @return int
     */
    public function getLimit()
    {
        $result = null;
        if (isset($this->_options['limit'])) {
            $result = $this->_options['limit'];
        }

        return $result;
    }

    /**
     * Field required flag setter.
     *
     * @param bool $required field required flag
     * @return void
     */
    public function setRequired($required = null)
    {
        if ($required !== null) {
            // Flip $required into allow null flag
            $this->_options['null'] = !$required;
        }
    }

    /**
     * Field required flag getter.
     *
     * @return bool
     */
    public function getRequired()
    {
        $result = null;
        if (isset($this->_options['null'])) {
            // Flip allow null flag into $required
            $result = !$this->_options['null'];
        }

        return $result;
    }

    /**
     * Field non-searchable flag setter.
     *
     * @param bool $nonSearchable field non-searchable flag
     * @return void
     */
    public function setNonSearchable($nonSearchable)
    {
        $this->_nonSearchable = $nonSearchable;
    }

    /**
     * Field non-searchable flag getter.
     *
     * @return bool
     */
    public function getNonSearchable()
    {
        return $this->_nonSearchable;
    }

    /**
     * Field unique flag setter.
     *
     * @param string $unique field unique flag
     * @return void
     */
    public function setUnique($unique)
    {
        $this->_unique = $unique;
    }

    /**
     * Field unique flag getter.
     *
     * @return bool
     */
    public function getUnique()
    {
        return $this->_unique;
    }
}
