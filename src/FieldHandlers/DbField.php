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
     * field limit
     *
     * @var int
     */
    protected $_limit;

    /**
     * field required flag
     *
     * @var bool
     */
    protected $_required;

    /**
     * field non-searchable flag
     *
     * @var bool
     */
    protected $_nonSearchable;

    /**
     * Supported field types
     *
     * @var array
     */
    protected $_supportedTypes = ['uuid', 'string', 'integer', 'boolean', 'text', 'datetime', 'date', 'time'];

    /**
     * Constructor
     *
     * @param string $name          field name
     * @param string $type          field type
     * @param int    $limit         field limit
     * @param bool   $required      field required flag
     * @param bool   $nonSearchable field non-searchable flag
     */
    public function __construct($name, $type, $limit, $required, $nonSearchable)
    {
        $this->setName($name);
        $this->setType($type);
        $this->setLimit($limit);
        $this->setRequired($required);
        $this->setNonSearchable($nonSearchable);

    }

    /**
     * Field name setter.
     *
     * @param string $name field name
     */
    public function setName($name)
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
     */
    public function setType($type)
    {
        if (empty($type)) {
            throw new InvalidArgumentException('Empty field type is not allowed');
        }

        if (!in_array($type, $this->_supportedTypes)) {
            throw new InvalidArgumentException('Unsupported field type: ' . $type);
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
     */
    public function setLimit($limit)
    {
        $this->_limit = $limit;
    }

    /**
     * Field limit getter.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * Field required flag setter.
     *
     * @param bool $required field required flag
     */
    public function setRequired($required)
    {
        $this->_required = $required;
    }

    /**
     * Field required flag getter.
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->_required;
    }

    /**
     * Field non-searchable flag setter.
     *
     * @param bool $nonSearchable field non-searchable flag
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
}
