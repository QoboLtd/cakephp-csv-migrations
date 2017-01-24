<?php
namespace CsvMigrations\FieldHandlers;

/**
 * CsvField
 *
 * This class defines the data and functionality
 * necessary for handling CSV field definitions in
 * a consistent way
 */
class CsvField
{
    /**
     * Type pattern
     *
     * CSV field types can be either simple types, like:
     *
     * * string
     * * boolean
     * * text
     *
     * Or types with limits, like:
     *
     * * string(100)
     * * related(Foobar)
     *
     * This pattern defines the syntax to match both.
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
     * Field name
     *
     * @var string
     */
    protected $_name;

    /**
     * Field type
     *
     * @var string
     */
    protected $_type;

    /**
     * Field limit
     *
     * @var int
     */
    protected $_limit;

    /**
     * Field required flag
     *
     * @var bool
     */
    protected $_required;

    /**
     * Field non-searchable flag
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
     * @param array string $row CSV row
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
     * Extract field type from type value
     *
     * In case of simple field, like text, this will
     * return the type itself.  In case of a type
     * with limit, it will strip the limit away and
     * return the type alone.
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
     * Extract field limit from type value
     *
     * In case of simple field, like text, this will
     * return the default limit.  In case of a type
     * with limit, it will strip the type away and
     * return the limit alone.
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
     * Set field name
     *
     * @param string $name field name
     * @return void
     */
    public function setName($name)
    {
        $name = (string)$name;
        if (empty($name)) {
            throw new \InvalidArgumentException('Empty field name is not allowed');
        }

        $this->_name = $name;
    }

    /**
     * Get field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set field type
     *
     * @param string $type field type
     * @return void
     */
    public function setType($type)
    {
        $type = (string)$type;
        if (empty($type)) {
            throw new \InvalidArgumentException(__CLASS__ . ': Empty field type is not allowed: ' . $this->getName());
        }

        $this->_type = $this->_extractType($type);
    }

    /**
     * Get field type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set field limit from type
     *
     * @param string $type field type
     * @return void
     */
    public function setLimit($type)
    {
        $type = (string)$type;
        if (empty($type)) {
            throw new \InvalidArgumentException('Empty field type is not allowed: ' . $this->getName());
        }

        $this->_limit = $this->_extractLimit($type);
    }

    /**
     * Get field limit
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * Get list name
     *
     * This is an alias of getLimit().
     *
     * @see CsvField::getLimit
     * @return string
     */
    public function getListName()
    {
        return $this->getLimit();
    }

    /**
     * Get association CSV module name
     *
     * This is an alias of getLimit().
     *
     * @see CsvField::getLimit
     * @return int
     */
    public function getAssocCsvModule()
    {
        return $this->_limit;
    }

    /**
     * Set field required flag
     *
     * @param bool $required field required flag
     * @return void
     */
    public function setRequired($required)
    {
        $this->_required = (bool)$required;
    }

    /**
     * Get field required flag
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->_required;
    }

    /**
     * Set field non-searchable flag
     *
     * @param bool $nonSearchable Field non-searchable flag
     * @return void
     */
    public function setNonSearchable($nonSearchable)
    {
        $this->_nonSearchable = (bool)$nonSearchable;
    }

    /**
     * Get field non-searchable flag
     *
     * @return bool
     */
    public function getNonSearchable()
    {
        return $this->_nonSearchable;
    }

    /**
     * Set field unique flag
     *
     * @param bool $unique field unique flag
     * @return void
     */
    public function setUnique($unique)
    {
        $this->_unique = (bool)$unique;
    }

    /**
     * Get field unique flag
     *
     * @return bool
     */
    public function getUnique()
    {
        return $this->_unique;
    }
}
