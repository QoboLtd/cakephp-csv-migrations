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
namespace CsvMigrations\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Utility\Inflector;
use CsvMigrations\Utility\Validate\Utility;
use RuntimeException;

class ValidateShell extends Shell
{
    /**
     * @var string $checkInterface Interface that all Check classes must implement
     */
    protected $checkInterface = 'CsvMigrations\\Utility\\Validate\\Check\\CheckInterface';

    /**
     * @var array $modules List of known modules
     */
    protected $modules;

    /**
     * Set shell description and command line options
     *
     * @return ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');
        $parser->description('Validate modules configuration');
        $parser->addArgument('modules', [
            'help' => 'Comma-separated list of modules to validate.  All will be checked if omitted.',
        ]);

        return $parser;
    }

    /**
     * Main method for shell execution
     *
     * @param string $modules Comma-separated list of module names to validate
     * @return void
     */
    public function main($modules = null)
    {
        $this->info('Checking modules configuration');
        $this->hr();

        $this->modules = Utility::getModules();

        if (empty($this->modules)) {
            $this->warn('Did not find any modules');
            exit();
        }

        $modules = empty($modules) ? $this->modules : explode(',', (string)$modules);
        $errorsCount = $this->validateModules($modules);
        if ($errorsCount > 0) {
            $this->abort("Errors found: $errorsCount.  Validation failed!");
        }
        $this->success('No errors found. Validation passed!');
    }

    /**
     * Validate a given list of modules
     *
     * @param array $modules List of module names to validate
     * @return int Count of errors found
     */
    protected function validateModules(array $modules)
    {
        $result = 0;

        foreach ($modules as $module) {
            $this->info("Checking module $module", 2);

            $moduleResult = $this->runModuleChecks($module);

            $result += count($moduleResult['errors']);
            $this->printMessages('warning', $moduleResult['warnings']);
            $this->printMessages('error', $moduleResult['errors']);
            $this->hr();
        }

        return $result;
    }

    /**
     * Run validation checks for a given module
     *
     * @param string $module Module name
     * @return array Array with errors and warnings
     */
    protected function runModuleChecks($module)
    {
        $result = [
            'errors' => [],
            'warnings' => [],
        ];

        if (!in_array($module, $this->modules)) {
            $result['errors'][] = "$module is not a known module";

            return $result;
        }

        $options = $this->getModuleOptions($module);
        $checks = empty($options['checks']) ? [] : $options['checks'];

        if (empty($checks)) {
            $result['warnings'][] = "No checks configured for module [$module]";

            return $result;
        }

        foreach ($checks as $check => $options) {
            $this->out(" - Running $check ... ", 0);

            try {
                $check = $this->getCheckInstance($check);
                $checkResult = $check->run($module, $options);
            } catch (RuntimeException $e) {
                $result['errors'][] = $e->getMessage();
                $this->printCheckStatus(1);
                continue;
            }

            $result['errors'] = array_merge($result['errors'], $check->getErrors());
            $result['warnings'] = array_merge($result['warnings'], $check->getWarnings());

            $this->printCheckStatus($checkResult);
        }

        return $result;
    }

    /**
     * Get ValidateShell configuration for a given module
     *
     * If no options configured for the given module, return
     * the default options instead (aka options for module
     * '_default').
     *
     * @param string $module Module name
     * @return  array
     */
    protected function getModuleOptions($module)
    {
        $default = Configure::read('CsvMigrations.ValidateShell.module._default');
        $result = Configure::read('CsvMigrations.ValidateShell.module.' . $module);
        $result = empty($result) ? $default : $result;

        return $result;
    }

    /**
     * Get an instance of a given check class
     *
     * @throws \RuntimeException when class does not exist or is invalid
     * @param string $checkClass Name of the check class
     * @return \CsvMigrations\Utility\Validate\Check\CheckInterface
     */
    protected function getCheckInstance($checkClass)
    {
        $checkClass = (string)$checkClass;

        if (!class_exists($checkClass)) {
            throw new RuntimeException("Check class [$checkClass] does not exist");
        }

        if (!in_array($this->checkInterface, array_keys(class_implements($checkClass)))) {
            throw new RuntimeException("Check class [$checkClass] does not implement [" . $this->checkInterface . "]");
        }

        return new $checkClass();
    }

    /**
     * Print check status (OK or FAIL)
     *
     * If the count of errors is greater than zero,
     * then print FAIL.  OK otherwise.
     *
     * @param int $errorCount Count of errors
     * @return void
     */
    protected function printCheckStatus($errorCount)
    {
        if ($errorCount <= 0) {
            $this->success('OK');

            return;
        }

        $this->out($this->wrapMessageWithType('error', 'FAIL'));
    }

    /**
     * Print messages of a given type
     *
     * @param string $type Type of messages (info, error, warning, etc)
     * @param array $messages Array of messages to report
     * @return void
     */
    protected function printMessages($type, array $messages = [])
    {
        $this->out('');

        $plural = Inflector::pluralize($type);
        if (empty($messages)) {
            $this->success("No $plural found in module.");

            return;
        }

        // Minimize output to only unique messages
        $messages = array_unique($messages);

        $plural = ucfirst($plural);
        $this->out($this->wrapMessageWithType($type, "$plural (" . count($messages) . "):"));

        // Remove ROOT path for shorter output
        $messages = preg_replace('#' . ROOT . DS . '#', '', $messages);
        // Prefix all messages as list items
        $messages = preg_replace('/^/', ' - ', $messages);

        $this->out($this->wrapMessageWithType($type, $messages));
    }
}
