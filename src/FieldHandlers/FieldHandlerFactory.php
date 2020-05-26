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

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Cake\View\View;
use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use CsvMigrations\HasFieldsInterface;
use InvalidArgumentException;
use RuntimeException;

class FieldHandlerFactory
{
    /**
     * View instance.
     *
     * @var \Cake\View\View|null
     */
    public $cakeView = null;

    /**
     * Constructor.
     *
     * @param \Cake\View\View|null $cakeView CakePHP view instance
     */
    public function __construct(?View $cakeView = null)
    {
        $this->cakeView = $cakeView;
    }

    /**
     * Get an instance of field handler for given table field.
     *
     * @param mixed $table Table name or instance of \Cake\ORM\Table
     * @param string $field Field name
     * @param mixed[] $options Field handler options
     * @param \Cake\View\View|null $view CakePHP view instance
     * @return \CsvMigrations\FieldHandlers\FieldHandlerInterface
     */
    public static function getByTableField($table, string $field, array $options = [], ?View $view = null): FieldHandlerInterface
    {
        $table = is_string($table) ? TableRegistry::getTableLocator()->get($table) : $table;
        $handler = self::getHandler($table, $field, $options, $view);

        return $handler;
    }

    /**
     * Render field form input.
     *
     * @param mixed $table Name or instance of the Table
     * @param string $field Field name
     * @param mixed $data Field data
     * @param mixed[] $options Field options
     * @return string Field input
     */
    public function renderInput($table, string $field, $data = '', array $options = []): string
    {
        $handler = self::getByTableField($table, $field, $options, $this->cakeView);

        return $handler->renderInput($data, $options);
    }

    /**
     * Render field form label.
     *
     * @param mixed $table Name or instance of the Table
     * @param string $field Field name
     * @param mixed[] $options Field options
     * @return string Field input
     */
    public function renderName($table, string $field, array $options = []): string
    {
        $handler = self::getByTableField($table, $field, $options, $this->cakeView);

        return $handler->renderName();
    }

    /**
     * Get search options.
     *
     * @param mixed $table Name or instance of the Table
     * @param string $field Field name
     * @param mixed[] $options Field options
     * @return mixed[] Array of fields and their options
     */
    public function getSearchOptions($table, string $field, array $options = []): array
    {
        $handler = self::getByTableField($table, $field, $options, $this->cakeView);

        return $handler->getSearchOptions($options);
    }

    /**
     * Render field value.
     *
     * @param mixed $table Name or instance of the Table
     * @param string $field Field name
     * @param mixed $data Field data
     * @param mixed[] $options Field options
     * @return string
     */
    public function renderValue($table, string $field, $data, array $options = []): string
    {
        $handler = self::getByTableField($table, $field, $options, $this->cakeView);

        return $handler->renderValue($data, $options);
    }

    /**
     * Get Default Render Value from Renderers
     *
     * @param mixed $table Name or instance of the Table
     * @param string $field Field name
     * @param mixed[] $options Field options
     * @return mixed
     */
    public function getDefaultValue($table, string $field, array $options = [])
    {
        $handler = self::getByTableField($table, $field, $options, $this->cakeView);

        return $handler->getDefaultValue();
    }

    /**
     * Validation rules setter.
     *
     * @param mixed $table Name or instance of the Table
     * @param string $field Field name
     * @param \Cake\Validation\Validator $validator Validator instance
     * @param mixed[] $options Field options
     * @return \Cake\Validation\Validator
     */
    public function setValidationRules($table, string $field, Validator $validator, array $options = []): Validator
    {
        $handler = self::getByTableField($table, $field);
        $validator = $handler->setValidationRules($validator, $options);
        if (! $validator instanceof Validator) {
            throw new RuntimeException(
                sprintf('Field Handler returned value must be an instance of %s.', Validator::class)
            );
        }

        return $validator;
    }

    /**
     * Convert field CSV into database fields
     *
     * **NOTE** For the time-being, we are not utilizing $table and $field
     *          parameters.  They are here to ease the near-future refactoring
     *          of the FieldHandlerFactory class into a proper (and simple)
     *          factory.
     *
     * @param \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @param mixed $table Name or instance of the Table
     * @param string $field Field name
     * @return mixed[] list of DbField instances
     */
    public function fieldToDb(CsvField $csvField, $table, string $field = ''): array
    {
        if ('' === $field) {
            $field = $csvField->getName();
        }
        // No options or view is necessary for the fieldToDb currently
        $handler = self::getByTableField($table, $field);

        return $handler->fieldToDb($csvField);
    }

    /**
     * Get field handler instance
     *
     * This method returns an instance of the appropriate
     * FieldHandler class.
     *
     * @throws \RuntimeException when failed to instantiate field handler
     * @param Table $table Table instance
     * @param string|array $field Field name
     * @param mixed[] $options Field options
     * @param \Cake\View\View $view Optional CakePHP view instance
     * @return \CsvMigrations\FieldHandlers\FieldHandlerInterface
     */
    protected static function getHandler(Table $table, $field, array $options = [], View $view = null): FieldHandlerInterface
    {
        if (empty($field)) {
            throw new InvalidArgumentException("Field parameter is empty");
        }

        // Save field name
        $fieldName = '';
        if (is_string($field)) {
            $fieldName = $field;
        }

        // Overwrite field with field difinitions options
        if (!empty($options['fieldDefinitions'])) {
            $field = $options['fieldDefinitions'];
        }

        // Prepare the stub field
        $stubFields = [];

        if (is_string($field)) {
            $stubFields = self::getStubFromString($fieldName);
        }
        if (is_array($field)) {
            $stubFields = self::getStubFromArray($fieldName, $field);
        }

        if (empty($stubFields)) {
            throw new InvalidArgumentException("Field can be either a string or an associative array");
        }

        $fieldDefinitions = $stubFields;
        if ($table instanceof HasFieldsInterface) {
            $fieldDefinitions = $table->getFieldsDefinitions($stubFields);
        }

        if (empty($fieldDefinitions[$fieldName])) {
            throw new RuntimeException("Failed to get definition for field '$fieldName'");
        }

        $field = new CsvField($fieldDefinitions[$fieldName]);
        $fieldType = $field->getType();

        $config = ConfigFactory::getByType($fieldType, $fieldName, $table);
        if ($view) {
            $config->setView($view);
        }

        return new FieldHandler($config);
    }

    /**
     * Get stub fields from a field name string
     *
     * @param string $fieldName Field name
     * @return mixed[] Stub fields
     */
    protected static function getStubFromString(string $fieldName): array
    {
        return [
            $fieldName => [
                'name' => $fieldName,
                'type' => 'string',
            ],
        ];
    }

    /**
     * Get stub fields from a field array
     *
     * @throws \InvalidArgumentException when field name or type are missing
     * @param string $fieldName Field name
     * @param mixed[] $field Field array
     * @return mixed[] Stub fields
     */
    protected static function getStubFromArray(string $fieldName, array $field): array
    {
        // Try our best to find the field name
        if (empty($field['name']) && !empty($fieldName)) {
            $field['name'] = $fieldName;
        }

        if (empty($field['name'])) {
            throw new InvalidArgumentException("Field array is missing 'name' key");
        }
        if (empty($field['type'])) {
            throw new InvalidArgumentException("Field array is missing 'type' key");
        }

        return [
            $field['name'] => $field,
        ];
    }
}
