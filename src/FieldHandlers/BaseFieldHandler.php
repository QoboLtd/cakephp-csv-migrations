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

use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\View\View;
use CsvMigrations\Event\EventName;
use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use RuntimeException;

/**
 * BaseFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to all field handlers.
 *
 * NOTE: Try to avoid inheriting from this class directly.
 *       Instead, use one of the more specific base classes.
 *
 * @abstract
 */
abstract class BaseFieldHandler implements FieldHandlerInterface
{
    /**
     * Default database field type
     */
    const DB_FIELD_TYPE = 'string';

    /**
     * Flag for rendering value as is
     */
    const RENDER_PLAIN_VALUE = 'plain';

    /**
     * Default options
     *
     * @var array
     */
    public $defaultOptions = [];

    /**
     * @var string $defaultConfigClass Config class to use as default
     */
    protected static $defaultConfigClass = '\\CsvMigrations\\FieldHandlers\\Config\\StringConfig';

    /**
     * @var $config \CsvMigrations\FieldHandlers\Config\ConfigInterface Configuration
     */
    protected $config;

    /**
     * Constructor
     *
     * @param mixed  $table    Name or instance of the Table
     * @param string $field    Field name
     * @param \Cake\View\View|null $view     Optional instance of the View
     */
    public function __construct($table, $field, $view = null)
    {
        $this->setConfig($table, $field, $view);
        $this->setDefaultOptions();
    }

    /**
     * Set field handler config
     *
     * @param mixed  $table    Name or instance of the Table
     * @param string $field    Field name
     * @param \Cake\View\View|null $view     Optional instance of the View
     * @return void
     */
    protected function setConfig($table, $field, $view)
    {
        $this->config = new static::$defaultConfigClass($field, $table);
        if ($view instanceof View) {
            $this->config->setView($view);
        }
    }

    /**
     * Set default options
     *
     * Populate the $defaultOptions to make sure we always have
     * the fieldDefinitions options for the current field.
     *
     * @return void
     */
    protected function setDefaultOptions()
    {
        $this->setDefaultFieldOptions();
        $this->setDefaultFieldDefinitions();
        $this->setDefaultLabel();
        $this->setDefaultValue();
    }

    /**
     * Set default field options from config
     *
     * Read fields.ini configuration file and if there are any
     * options defined for the current field, use them as defaults.
     *
     * @return void
     */
    protected function setDefaultFieldOptions()
    {
        $table = $this->config->getTable();
        $field = $this->config->getField();

        $mc = new ModuleConfig(ConfigType::FIELDS(), Inflector::camelize($table->table()));
        $config = (array)json_decode(json_encode($mc->parse()), true);
        if (!empty($config[$field])) {
            $this->defaultOptions = array_replace_recursive($this->defaultOptions, $config[$field]);
        }
    }

    /**
     * Set default field label
     *
     * NOTE: This should only be called AFTER the setDefaultFieldOptions()
     *       which reads fields.ini values, which might include the label
     *       option.
     *
     * @return void
     */
    protected function setDefaultLabel()
    {
        $this->defaultOptions['label'] = $this->renderName();
    }

    /**
     * Set default field definitions
     *
     * @return void
     */
    protected function setDefaultFieldDefinitions()
    {
        $table = $this->config->getTable();
        $field = $this->config->getField();

        // set $options['fieldDefinitions']
        $stubFields = [
            $field => [
                'name' => $field,
                'type' => self::DB_FIELD_TYPE, // not static:: to preserve string
            ],
        ];
        if (method_exists($table, 'getFieldsDefinitions') && is_callable([$table, 'getFieldsDefinitions'])) {
            $fieldDefinitions = $table->getFieldsDefinitions($stubFields);
            $this->defaultOptions['fieldDefinitions'] = new CsvField($fieldDefinitions[$field]);
        }

        // This should never be the case, except, maybe
        // for some unit test runs or custom non-CSV
        // modules.
        if (empty($this->defaultOptions['fieldDefinitions'])) {
            $this->defaultOptions['fieldDefinitions'] = new CsvField($stubFields[$field]);
        }
    }

    /**
     * Set default field value
     *
     * @return void
     */
    protected function setDefaultValue()
    {
        if (empty($this->defaultOptions['default'])) {
            return;
        }

        // If we have a default value from configuration, pass it through
        // processing for magic/dynamic values like dates and usernames.
        $eventName = (string)EventName::FIELD_HANDLER_DEFAULT_VALUE();
        $event = new Event($eventName, $this, [
            'default' => $this->defaultOptions['default']
        ]);

        $view = $this->config->getView();
        $view->eventManager()->dispatch($event);

        // Only overwrite the default if any events were triggered
        $listeners = $view->eventManager()->listeners($eventName);
        if (empty($listeners)) {
            return;
        }
        $this->defaultOptions['default'] = $event->result;
    }

    /**
     * Fix provided options
     *
     * This method is here to fix some issues with backward
     * compatibility and make sure that $options parameters
     * are consistent throughout.
     *
     * @param array  $options Options to fix
     * @return array          Fixed options
     */
    protected function fixOptions(array $options = [])
    {
        $result = $options;
        if (empty($result)) {
            return $result;
        }

        if (empty($result['fieldDefinitions'])) {
            return $result;
        }

        if (!is_array($result['fieldDefinitions'])) {
            return $result;
        }

        // Sometimes, when setting fieldDefinitions manually to render a particular
        // type, the name is omitted.  This works for an array, but doesn't work for
        // the CsvField instance, as the name is required.  Gladly, we know the name
        // and can fix it easily.
        if (empty($result['fieldDefinitions']['name'])) {
            $result['fieldDefinitions']['name'] = $this->config->getField();
        }

        // Previously, fieldDefinitions could be either an array or a CsvField instance.
        // Now we expect it to always be a CsvField instance.  So, if we have a non-empty
        // array, then instantiate CsvField with the values from it.
        $result['fieldDefinitions'] = new CsvField($result['fieldDefinitions']);

        return $result;
    }

    /**
     * Render field input
     *
     * This method prepares the form input for the given field,
     * including the input itself, label, pre-populated value,
     * and so on.  The result can be controlled via the variety
     * of options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field input HTML
     */
    public function renderInput($data = '', array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));

        // Workaround for BLOBs
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }
        $data = $this->getFieldValueFromData($data, $options);

        if (empty($data) && !empty($options['default'])) {
            $data = $options['default'];
        }

        $options['label'] = !isset($options['label']) ? $this->renderName() : $options['label'];

        $searchOptions = $this->config->getProvider('renderInput');
        $searchOptions = new $searchOptions($this->config);
        $result = $searchOptions->provide($data, $options);

        return $result;
    }

    /**
     * Get options for field search
     *
     * This method prepares an array of search options, which includes
     * label, form input, supported search operators, etc.  The result
     * can be controlled with a variety of options.
     *
     * @param  array  $options Field options
     * @return array           Array of field input HTML, pre and post CSS, JS, etc
     */
    public function getSearchOptions(array $options = [])
    {
        $result = [];

        $options = array_merge($this->defaultOptions, $this->fixOptions($options));

        if ($options['fieldDefinitions']->getNonSearchable()) {
            return $result;
        }

        $options['label'] = empty($options['label']) ? $this->renderName() : $options['label'];

        $searchOptions = $this->config->getProvider('searchOptions');
        $searchOptions = new $searchOptions($this->config);
        $result = $searchOptions->provide(null, $options);

        return $result;
    }

    /**
     * Render field name
     *
     * @return string
     */
    public function renderName()
    {
        $label = !empty($this->defaultOptions['label']) ? $this->defaultOptions['label'] : '';

        $renderer = $this->config->getProvider('renderName');
        $renderer = new $renderer($this->config);
        $result = $renderer->provide($label);

        return $result;
    }

    /**
     * Render field value
     *
     * This method prepares the output of the value for the given
     * field.  The result can be controlled via the variety of
     * options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field value
     */
    public function renderValue($data, array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
        $result = $this->getFieldValueFromData($data, $options);

        // Currently needed for blobs from the database, but might be handy later
        // for network data and such.
        // TODO: Add support for encoding (base64, et) via $options
        if (is_resource($result)) {
            $result = stream_get_contents($result);
        }

        $rendererClass = $this->config->getProvider('renderValue');
        if (!empty($options['renderAs'])) {
            $rendererClass = __NAMESPACE__ . '\\Provider\\RenderValue\\' . ucfirst($options['renderAs']) . 'Renderer';
        }

        if (!class_exists($rendererClass)) {
            throw new InvalidArgumentException("Renderer class [$rendererClass] does not exist");
        }

        $rendererClass = new $rendererClass($this->config);
        $result = (string)$rendererClass->provide($result, $options);

        return $result;
    }

    /**
     * Convert CsvField to one or more DbField instances
     *
     * Simple fields from migrations CSV map one-to-one to
     * the database fields.  More complex fields can combine
     * multiple database fields for a single CSV entry.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array                                           DbField instances
     */
    public static function fieldToDb(CsvField $csvField)
    {
        // Temporary dummy configuration
        $config = new static::$defaultConfigClass('dummy_field');
        $fieldToDb = $config->getProvider('fieldToDb');
        $fieldToDb = new $fieldToDb($config);
        $result = $fieldToDb->provide($csvField);

        return $result;
    }

    /**
     * Get field value from given data
     *
     * @param mixed  $data  Variable to extract value from
     * @param array  $options Field options
     * @return mixed
     */
    protected function getFieldValueFromData($data, array $options)
    {
        $fieldValue = $this->config->getProvider('fieldValue');
        $fieldValue = new $fieldValue($this->config);
        $result = $fieldValue->provide($data, $options);

        return $result;
    }
}
