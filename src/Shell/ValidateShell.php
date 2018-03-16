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
use Cake\Utility\Inflector;
use CsvMigrations\Utility\Validate\Check;
use CsvMigrations\Utility\Validate\Utility;
use Exception;

class ValidateShell extends Shell
{
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

        $checks = Check::getList($module);

        if (empty($checks)) {
            $result['warnings'][] = "No checks configured for module [$module]";

            return $result;
        }

        foreach ($checks as $check => $options) {
            $this->out(" - Running $check ... ", 0);

            try {
                $check = Check::getInstance($check);
                $checkResult = $check->run($module, $options);
            } catch (Exception $e) {
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
