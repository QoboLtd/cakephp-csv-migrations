<?php
/**
 * This class represents the panel of each module. Panel is a group of input fields
 * which can be used to manipulate them.
 *
 * Copyright (c) 2016 Qobo. (http://qobo.biz)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program;
 *
 * @copyright     Copyright (c) 2016 Qobo.
 * @link          http://qobo.biz
 * @license       https://opensource.org/licenses/gpl-2.0.php
 */
namespace CsvMigrations;

use Cake\ORM\Entity;
use Cake\Utility\Hash;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use \InvalidArgumentException;
use \RuntimeException;

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
     * @param array $config Table's config
     */
    public function __construct($name, array $config)
    {
        $this->setName($name);
        $this->setExpression($config);
        $this->setFields();
    }

    /**
     * Getter of panel name.
     *
     * @return string panel name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter of panel name.
     *
     * @param  string $name Panel name
     * @return void
     */
    public function setName($name)
    {
        if (!$name) {
            throw new RuntimeException('Panel name not found therefore the object cannot be created');
        }
        $this->name = $name;
    }

    /**
     * Getter of expression
     *
     * @param  bool $clean Flag for removing the expression tokens
     * @return string expression
     */
    public function getExpression($clean = false)
    {
        if ($clean) {
            //Clean up expression from placeholder tokens.
            return str_replace(self::EXP_TOKEN, '', $this->expression);
        }

        return $this->expression;
    }

    /**
     * Setter of expression.
     *
     * @param  array $config Table's config
     * @return void
     */
    public function setExpression(array $config)
    {
        $panels = Hash::get($config, self::PANELS);
        $exp = Hash::get($panels, $this->getName());
        $this->expression = $exp;
    }

    /**
     * Getter of fields
     *
     * @return array fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Setter of fields.
     *
     * @return void
     */
    public function setFields()
    {
        preg_match_all('#' . self::EXP_TOKEN . '(.*?)' . self::EXP_TOKEN . '#', $this->getExpression(), $matches);
        if (empty($matches)) {
            throw new InvalidArgumentException(sprintf('Please wrap your placeholders with ' . self::EXP_TOKEN . ': %s', $exp));
        }
        $this->fields = $matches[1];
    }

    /**
     * Returns field values from the given entity.
     *
     * @param  Entity $entity Entity holding data
     * @return array          Associative array, Keys: placeholders Values: values
     */
    public function getFieldValues(Entity $entity)
    {
        $result = [];
        foreach ($this->getFields() as $f) {
            $result[$f] = $entity->get($f);
        }

        return $result;
    }

    /**
     * Evaluate the expression.
     *
     * @param  Entity $entity to get the values for placeholders
     * @return bool           True if it matches, false otherwise.
     */
    public function evalExpression(Entity $entity)
    {
        $language = new ExpressionLanguage();
        $values = $this->getFieldValues($entity);
        $eval = $language->evaluate($this->getExpression(true), $values);

        return $eval;
    }

    /**
     * Returns panel names.
     *
     * @param  array  $config Table's config
     * @return array|bool  Panel names or false
     */
    public static function getPanelNames(array $config)
    {
        if (empty($config[self::PANELS])) {
            return false;
        }

        return array_keys($config[self::PANELS]);
    }
}
