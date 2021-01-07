<?php
declare(strict_types=1);

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
use Cake\Utility\Hash;
use CsvMigrations\Event\EventName;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
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
     * Panel name
     * @var string
     */
    private $name;

    /**
     * Expression
     * @var string
     */
    private $expression;

    /**
     * @var ExpressionFunctionProviderInterface[]
     */
    private static $providers = [];

    /**
     * @param ExpressionFunctionProviderInterface $provider Provider
     *
     * @return void
     */
    public static function registerFunctionProvider(ExpressionFunctionProviderInterface $provider): void
    {
        self::$providers[] = $provider;
    }

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
     * Setter of expression.
     *
     * @param mixed[] $config Table's config
     * @return void
     */
    public function setExpression(array $config): void
    {
        $panels = Hash::get($config, self::PANELS);
        $exp = Hash::get($panels, $this->getName());

        // Simply strip variable placeholders
        $exp = str_replace('%%', '', $exp);

        $this->expression = $exp;
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
        $language = new ExpressionLanguage(null, self::$providers);

        return $language->evaluate($this->expression, array_merge($data, $extras));
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
