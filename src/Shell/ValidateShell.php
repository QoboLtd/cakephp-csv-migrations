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
use CsvMigrations\Utility\Validate\Utility;
use RuntimeException;

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
        $this->out('Checking modules configuration');
        $this->hr();

        $this->modules = Utility::getModules();

        if (empty($this->modules)) {
            $this->out('<warning>Did not find any modules</warning>');
            exit();
        }

        $modules = empty($modules) ? $this->modules : explode(',', (string)$modules);
        $errorsCount = $this->validateModules($modules);
        if ($errorsCount > 0) {
            $this->abort("Errors found: $errorsCount.  Validation failed!");
        }
        $this->out('<success>No errors found. Validation passed!</success>');
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

        $defaultOptions = Configure::read('CsvMigrations.ValidateShell.module._default');
        foreach ($modules as $module) {
            $errors = [];
            $warnings = [];

            $this->out("Checking module $module", 2);

            $moduleOptions = Configure::read('CsvMigrations.ValidateShell.module.' . $module);
            $moduleOptions = empty($moduleOptions) ? $defaultOptions : $moduleOptions;

            $checks = $moduleOptions['checks'];

            if (!in_array($module, $this->modules)) {
                $errors[] = "$module is not a known module";
            } else {
                foreach ($checks as $check => $options) {
                    $this->out(" - Running $check ... ", 0);

                    if (!class_exists($check)) {
                        throw new RuntimeException("Check class [$check] does not exist");
                    }
                    $interface = 'CsvMigrations\\Utility\\Validate\\Check\\CheckInterface';
                    if (!in_array($interface, array_keys(class_implements($check)))) {
                        throw new RuntimeException("Check class [$check] does not implement [$interface]");
                    }
                    $check = new $check();
                    $checkResult = $check->run($module, $options);
                    $errors = array_merge($errors, $check->getErrors());
                    $warnings = array_merge($warnings, $check->getWarnings());

                    $checkResult = $checkResult <= 0 ? '<success>OK</success>' : '<error>FAIL</error>';
                    $this->out($checkResult);
                }
            }

            $result += count($errors);
            $this->_printCheckStatus($errors, $warnings);
        }

        return $result;
    }

    /**
     * Print the status of a particular check
     *
     * @param array $errors Array of errors to report
     * @param array $warnings Array of warnings to report
     * @return void
     */
    protected function _printCheckStatus(array $errors = [], array $warnings = [])
    {
        $this->out('');

        // Print out warnings first, if any
        if (!empty($warnings)) {
            $this->out('Warnings:');
            foreach ($warnings as $warning) {
                $this->out('<warning> - ' . $warning . '</warning>');
            }
            $this->out('');
        }

        // Print success or list of errors, if any
        if (empty($errors)) {
            $this->out('<success>All OK</success>');
        } else {
            $this->out('Errors:');
            foreach ($errors as $error) {
                $this->out('<error> - ' . $error . '</error>');
            }
        }
        $this->hr();
    }
}
