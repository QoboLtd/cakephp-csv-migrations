<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use InvalidArgumentException;

class CsvField
{
    /**
     * Type and limit matching pattern.
     * Examples: string(100) or uuid or list(currencies)
     */
    const PATTERN_TYPE = '/(.*?)\((.*?)\)/';

    /**
     * Field name property
     */
    const FIELD_NAME = 'name';

    /**
     * Field type property
     */
    const FIELD_TYPE = 'type';

    /**
     * Field limit property
     */
    const FIELD_LIMIT = 'limit';

    /**
     * Field required property
     */
    const FIELD_REQUIRED = 'required';

    /**
     * Field non-searchable property
     */
    const FIELD_NON_SEARCHABLE = 'non-searchable';

    /**
     * Field unique property
     */
    const FIELD_UNIQUE = 'unique';

    /**
     * Default value for field type
     */
    const DEFAULT_FIELD_TYPE = 'string';

    /**
     * Default value for field limit
     */
    const DEFAULT_FIELD_LIMIT = null;

    /**
     * Default value for field required
     */
    const DEFAULT_FIELD_REQUIRED = false;

    /**
     * Default value for field non-searchable
     */
    const DEFAULT_FIELD_NON_SEARCHABLE = false;

    /**
     * Default value for field unique
     */
    const DEFAULT_FIELD_UNIQUE = false;

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
     * field unique flag
     *
     * @var bool
     */
    protected $_unique;

    /**
     * Constructor
     *
     * @param array string $row csv row
     */
    public function __construct(array $row)
    {
        // Merge row values with defaults
        $defaults = $this->_getDefaults();
        $row = array_merge($defaults, $row);

        $this->setName($row[static::FIELD_NAME]);
        $this->setType($row[static::FIELD_TYPE]);
        $this->setLimit($row[static::FIELD_TYPE]);
        $this->setRequired($row[static::FIELD_REQUIRED]);
        $this->setNonSearchable($row[static::FIELD_NON_SEARCHABLE]);
        $this->setUnique($row[static::FIELD_UNIQUE]);
    }

    /**
     * Get default values
     *
     * @return array
     */
    protected function _getDefaults()
    {
        $result = [
            static::FIELD_TYPE => static::DEFAULT_FIELD_TYPE,
            static::FIELD_REQUIRED => static::DEFAULT_FIELD_REQUIRED,
            static::FIELD_NON_SEARCHABLE => static::DEFAULT_FIELD_NON_SEARCHABLE,
            static::FIELD_UNIQUE => static::DEFAULT_FIELD_UNIQUE,
        ];

        return $result;
    }

    /**
     * Extract field type from type value.
     *
     * @param  string $type field type
     * @return string       field type
     */
    protected function _extractType($type)
    {
        if (false !== strpos($type, '(')) {
            preg_match(static::PATTERN_TYPE, $type, $matches);
            $type = $matches[1];
        }

        return $type;
    }

    /**
     * Extract field limit from type value.
     *
     * @param  string $type field type
     * @return mixed        field limit
     */
    protected function _extractLimit($type)
    {
        if (false !== strpos($type, '(')) {
            preg_match(static::PATTERN_TYPE, $type, $matches);
            $limit = $matches[2];
        } else {
            $limit = static::DEFAULT_FIELD_LIMIT;
        }

        return $limit;
    }

    /**
     * Field name setter.
     *
     * @param string $name field name
     * @return void
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
     * @return void
     */
    public function setType($type)
    {
        if (empty($type)) {
            throw new InvalidArgumentException('Empty field type is not allowed: ' . $this->getName());
        }

        $type = $this->_extractType($type);

        if (!in_array($type, FieldHandlerFactory::getList())) {
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
     * @param string $type field type
     * @return void
     */
    public function setLimit($type)
    {
        if (empty($type)) {
            throw new InvalidArgumentException('Empty field type is not allowed: ' . $this->getName());
        }

        $limit = $this->_extractLimit($type);
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
     * The column limit is used also for the module association.
     *
     * @return int
     */
    public function getAssocCsvModule()
    {
        return $this->_limit;
    }

    /**
     * Field required flag setter.
     *
     * @param string $required field required flag
     * @return void
     */
    public function setRequired($required)
    {
        $this->_required = (bool)$required;
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
     * @param string $nonSearchable field non-searchable flag
     * @return void
     */
    public function setNonSearchable($nonSearchable)
    {
        $this->_nonSearchable = (bool)$nonSearchable;
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
        $this->_unique = (bool)$unique;
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
