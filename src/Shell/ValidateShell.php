<?php
namespace CsvMigrations\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use CsvMigrations\Parser\Csv\MigrationParser;
use CsvMigrations\Parser\Ini\Parser;
use CsvMigrations\Parser\Csv\ViewParser;
use CsvMigrations\PathFinder\ConfigPathFinder;
use CsvMigrations\PathFinder\MigrationPathFinder;
use CsvMigrations\PathFinder\ViewPathFinder;

class ValidateShell extends Shell
{
    /**
     * Set shell description and command line options
     *
     * @return ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');
        $parser->description('Validate CSV and configuration files of all CSV modules');

        return $parser;
    }

    /**
     * Main method for shell execution
     *
     * @return void
     */
    public function main()
    {
        $errorsCount = 0;

        $this->out('Checking CSV files and configurations');
        $this->hr();
        try {
            $modules = $this->_findCsvModules();
        } catch (\Exception $e) {
            $this->abort("Failed to find CSV modules: " . $e->getMessage());
        }

        if (empty($modules)) {
            $this->out('<warning>Did not find any CSV modules</warning>');
            exit();
        }

        $this->out('Found the following modules: ', 1, Shell::VERBOSE);
        foreach ($modules as $module => $path) {
            $this->out(' - ' . $module, 1, Shell::VERBOSE);
        }

        $errorsCount += $this->_checkConfigPresence($modules);
        $errorsCount += $this->_checkMigrationPresence($modules);
        $errorsCount += $this->_checkViewsPresence($modules);

        if ($errorsCount) {
            $this->abort("Errors found: $errorsCount.  Validation failed!");
        }
        $this->out('<success>No errors found. Validation passed!</success>');
    }

    /**
     * Find the list of CSV modules and their paths
     *
     * @return array List of modules and their paths
     */
    protected function _findCsvModules()
    {
        $result = [];

        $path = Configure::read('CsvMigrations.migrations.path');
        if (!is_readable($path)) {
            throw new \RuntimeException("[$path] is not readable");
        }
        if (!is_dir($path)) {
            throw new \RuntimeException("[$path] is not a directory");
        }

        foreach (new \DirectoryIterator($path) as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }
            $result[$fileinfo->getFilename()] = $fileinfo->getPathname();
        }
        asort($result);

        return $result;
    }

    /**
     * Print the status of a particular check
     *
     * @param array $errors Array of errors to report
     * @return void
     */
    protected function _printCheckStatus(array $errors = [])
    {
        $this->out('');
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

    /**
     * Check if config.ini file is present for each module
     *
     * @param array $modules List of modules to check
     * @return int Count of errors found
     */
    protected function _checkConfigPresence(array $modules = [])
    {
        $errors = [];

        $this->out('Trying to find and parse the config file:', 2);
        foreach ($modules as $module => $path) {
            $moduleErrors = [];
            $this->out(' - ' . $module . ' ... ', 0);
            try {
                $pathFinder = new ConfigPathFinder;
                $path = $pathFinder->find($module);
                $parser = new Parser;
                $config = $parser->parseFromPath($path);
            } catch (\Exception $e) {
                $path = $path ? '[' . $path . ']' : '';
                $moduleErrors[] = $module . " module configuration file problem: " . $e->getMessage();
            }
            $result = empty($moduleErrors) ? '<success>OK</success>' : '<error>FAIL</error>';
            $this->out($result);
            $errors = array_merge($errors, $moduleErrors);
        }
        $this->_printCheckStatus($errors);

        return count($errors);
    }

    /**
     * Check if migration.csv file is present for each module
     *
     * @param array $modules List of modules to check
     * @return int Count of errors found
     */
    protected function _checkMigrationPresence(array $modules = [])
    {
        $errors = [];

        $this->out('Trying to find and parse the migration file:', 2);
        foreach ($modules as $module => $path) {
            $moduleErrors = [];
            $this->out(' - ' . $module . ' ... ', 0);
            try {
                $pathFinder = new MigrationPathFinder;
                $path = $pathFinder->find($module);
                $parser = new MigrationParser;
                $result = $parser->parseFromPath($path);
            } catch (\Exception $e) {
                $this->out('<error>FAIL</error>');
                $path = $path ? '[' . $path . ']' : '';
                $moduleErrors[] = $module . " module migration file $path problem: " . $e->getMessage();
            }
            $result = empty($moduleErrors) ? '<success>OK</success>' : '<error>FAIL</error>';
            $this->out($result);
            $errors = array_merge($errors, $moduleErrors);
        }
        $this->_printCheckStatus($errors);

        return count($errors);
    }

    /**
     * Check if view files are present for each module
     *
     * @param array $modules List of modules to check
     * @return int Count of errors found
     */
    protected function _checkViewsPresence(array $modules = [])
    {
        $errors = [];

        $views = [
            'add',
            'edit',
            'view',
            'index',
        ];

        $this->out('Trying to find and parse the view files:', 2);
        foreach ($modules as $module => $path) {
            $moduleErrors = [];
            $viewCounter = 0;
            $this->out(' - ' . $module . ' ... ', 0);
            foreach ($views as $view) {
                $path = '';
                try {
                    $pathFinder = new ViewPathFinder;
                    $path = $pathFinder->find($module, $view);
                } catch (\Exception $e) {
                    // It's OK for view files to be missing.
                    // For example, Files and Users modules.
                }
                // If the view file does exist, it has to be parseable.
                if (file_exists($path)) {
                    $viewCounter++;
                    try {
                        $parser = new ViewParser;
                        $result = $parser->parseFromPath($path);
                    } catch (\Exception $e) {
                        $path = $path ? '[' . $path . ']' : '';
                        $moduleErrors[] = $module . " module [$view] migration file problem: " . $e->getMessage();
                    }
                }
            }
            $this->out('<info>' . (int) $viewCounter . ' views</info> ... ', 0);
            $result = empty($moduleErrors) ? '<success>OK</success>' : '<error>FAIL</error>';
            $this->out($result);
            $errors = array_merge($errors, $moduleErrors);
        }
        $this->_printCheckStatus($errors);

        return count($errors);
    }
}
