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
     * Field type can be either simple or combined
     * with limit.  For example:
     *
     * * Simple: uuid, string, date.
     * * Combined: string(100), list(Foo).
     *
     * In case of a simple type, it is returned as is.  For
     * the combined typed, the limit is stripped out and a
     * simple type only is returned.
     *
     * @param  string $type field type
     * @return string       field type
     */
    protected function _extractType($type)
    {
        if (preg_match(static::PATTERN_TYPE, $type, $matches)) {
            if (!empty($matches[1])) {
                $type = $matches[1];
            }
        }

        return $type;
    }

    /**
     * Extract field limit from type value
     *
     * Field type can be either simple or combined
     * with limit.  For example:
     *
     * * Simple: uuid, string, date.
     * * Combined: string(100), list(Foo).
     *
     * In case of a simple type, the default limit is
     * retured.  For the combined typed, the type is
     * stripped out and a limit only is returned.
     *
     * @param  string $type field type
     * @return mixed        field limit
     */
    protected function _extractLimit($type)
    {
        $limit = static::DEFAULT_FIELD_LIMIT;
        if (preg_match(static::PATTERN_TYPE, $type, $matches)) {
            if (!empty($matches[2])) {
                $limit = $matches[2];
            }
        }

        return $limit;
    }

    /**
     * Set field name
     *
     * @throws \InvalidArgumentException when name is empty
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
     * @throws \InvalidArgumentException when type is empty
     * @param  string $type field type
     * @return void
     */
    public function setType($type)
    {
        $type = (string)$type;
        if (empty($type)) {
            throw new \InvalidArgumentException('Empty field type is not allowed: ' . $this->getName());
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
     * Set field limit
     *
     * Type is set as is if it is null or integer.  If
     * it passses is_numeric() then it's cast to integer.
     * In all other cases, it is assumed that the limit is
     * a string defining field type, and limit needs to be
     * extracted.
     *
     * @param mixed $limit field limit
     * @return void
     */
    public function setLimit($limit)
    {
        if ($limit === null) {
            $this->_limit = $limit;

            return;
        }

        if (is_int($limit) || is_numeric($limit)) {
            $result = abs($limit);
            if ($result == 0) {
                $result = null;
            }
            $this->_limit = $result;

            return;
        }

        $result = (string)$limit;
        if (empty($result)) {
            throw new \InvalidArgumentException('Empty field type is not allowed: ' . $this->getName());
        }

        $this->_limit = $this->_extractLimit($result);
    }

    /**
     * Get field limit
     *
     * @return int|null
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
        return $this->getLimit();
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
