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

namespace CsvMigrations\Utility;

use ArrayObject;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Entity;
use Cake\Utility\Hash;
use CsvMigrations\Event\EventName;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * This class represents the panel of each module. Panel is a group of input fields
 * which can be used to manipulate them.
 */
class Panel
{
    /**
     * Key for the type of panels. It is used in the CsvMigration module config.
     */
    const PANELS = 'panels';

    /**
     * Token used in expression to distinquish placeholders.
     */
    const EXP_TOKEN = '%%';

    /**
     * Panel name
     * @var string
     */
    public $name;

    /**
     * Expression
     * @var string
     */
    public $expression;

    /**
     * Fields
     * @var array
     */
    public $fields = [];

    /**
     * Initializes a new instance
     *
     *
     * @param string $name Panel name
     * @param mixed[] $config Table's config
     */
    public function __construct(string $name, array $config)
    {
        $this->setName($name);
        $this->setExpression($config);
        $this->setFields();

        // Move all fields into an object
        foreach ($this->getFields() as $field) {
            $search = sprintf('%1$s%2$s%1$s', self::EXP_TOKEN, $field);
            $replace = sprintf('%1$sfields.%2$s%1$s', self::EXP_TOKEN, $field);
            $this->expression = str_replace($search, $replace, $this->expression);
        }
    }

    /**
     * Getter of panel name.
     *
     * @return string panel name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Setter of panel name.
     *
     * @param string $name Panel name
     * @return void
     */
    public function setName(string $name = ''): void
    {
        if (empty($name)) {
            throw new RuntimeException('Panel name not found therefore the object cannot be created');
        }
        $this->name = $name;
    }

    /**
     * Getter of expression
     *
     * @param bool $clean Flag for removing the expression tokens
     * @return string expression
     */
    public function getExpression(bool $clean = false): string
    {
        return $clean ?
            str_replace(self::EXP_TOKEN, '', $this->expression) : // clean up expression from placeholder tokens.
            $this->expression;
    }

    /**
     * Setter of expression.
     *
     * @param mixed[] $config Table's config
     * @return void
     */
    public function setExpression(array $config): void
    {
        $panels = Hash::get($config, self::PANELS);
        $exp = Hash::get($panels, $this->getName());
        $this->expression = $exp;
    }

    /**
     * Getter of fields
     *
     * @return string[] fields
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Setter of fields.
     *
     * @throws InvalidArgumentException
     * @return void
     */
    public function setFields(): void
    {
        preg_match_all('#' . self::EXP_TOKEN . '(.*?)' . self::EXP_TOKEN . '#', $this->getExpression(), $matches);
        if (empty($matches[1])) {
            throw new InvalidArgumentException("No tokens found in expression");
        }
        $this->fields = $matches[1];
    }

    /**
     * Returns field values from the given entity.
     *
     * @param mixed[] $data to get the values for placeholders
     * @return mixed[] Associative array, Keys: placeholders Values: values
     */
    public function getFieldValues(array $data): array
    {
        $result = [];
        foreach ($this->getFields() as $field) {
            $result[$field] = array_key_exists($field, $data) ? $data[$field] : null;
        }

        return $result;
    }

    /**
     * Evaluate the expression.
     *
     * @param mixed[] $data to get the values for placeholders
     * @param mixed[] $extras Extra variables to pass to expression language parser
     * @return bool True if it matches, false otherwise.
     */
    public function evalExpression(array $data, array $extras = []): bool
    {
        $extraVariables = new ArrayObject($extras);
        $language = new ExpressionLanguage();

        // Make the fields properties of an internal object
        $fields = $this->getFieldValues($data);
        $fields = json_decode((string)json_encode($fields));

        // Fire an event
        $event = new Event((string)EventName::PANEL_POPULATE_EXTRAS(), null, $extraVariables);
        EventManager::instance()->dispatch($event);

        $eval = $language->evaluate($this->getExpression(true), compact('fields') + (array)$extraVariables);

        return $eval;
    }

    /**
     * Returns panel names.
     *
     * @param mixed[] $config Table's config
     * @return mixed[]
     */
    public static function getPanelNames(array $config): array
    {
        if (empty($config[self::PANELS])) {
            return [];
        }

        return array_keys($config[self::PANELS]);
    }
}
