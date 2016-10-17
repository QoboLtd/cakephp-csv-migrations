<?php
namespace CsvMigrations\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use CsvMigrations\Parser\Csv\MigrationParser;

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
        try {
            $modules = $this->_findCsvModules();
        } catch (\Exception $e) {
            $this->abort("Failed to find CSV modules: " . $e->getMessage());
        }

        if (empty($modules)) {
            $this->out('<warning>Did not find any CSV modules</warning>');
            exit();
        }

        $this->out('Found the following modules: ');
        foreach ($modules as $module => $path) {
            $this->out($module);
        }

        $errorsCount += $this->_checkConfigPresence($modules);
        $errorsCount += $this->_checkMigrationPresence($modules);
        $errorsCount += $this->_checkMigrationParser($modules);

        if ($errorsCount) {
            $this->abort('Errors found [' . $errorsCount . '].  Validation failed!');
        }
        $this->out('<info>No errors found. Validation passed!</info>');
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
        if (empty($errors)) {
            $this->out('<info>All OK</info>');
        } else {
            foreach ($errors as $error) {
                $this->out('<error>' . $error . '</error>');
            }
        }
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

        $this->out('Checking the presence of configuration file');

        $configFile = 'config.ini';
        foreach ($modules as $module => $path) {
            $path .= DIRECTORY_SEPARATOR . $configFile;
            if (!file_exists($path)) {
                $errors[] = $module . " module is missing configuration file at [$path]";
            }
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

        $this->out('Checking the presence of migration file');
        $migrationFile = 'migration.csv';
        foreach ($modules as $module => $path) {
            $path .= DIRECTORY_SEPARATOR . $migrationFile;
            if (!file_exists($path)) {
                $errors[] = $module . " module is missing migration file at [$path]";
            }
        }
        $this->_printCheckStatus($errors);

        return count($errors);
    }

    /**
     * Check if migration.csv file is can be parsed for each module
     *
     * @param array $modules List of modules to check
     * @return int Count of errors found
     */
    protected function _checkMigrationParser(array $modules = [])
    {
        $errors = [];

        $this->out('Checking if migration file is parseable');
        $migrationFile = 'migration.csv';
        foreach ($modules as $module => $path) {
            $path .= DIRECTORY_SEPARATOR . $migrationFile;
            $parser = new MigrationParser();
            try {
                $csvData = $parser->parseFromPath($path);
            } catch (\Exception $e) {
                $errors[] = 'Migration file parsing failed for [' . $module . ']: ' . $e->getMessage();
            }
        }
        $this->_printCheckStatus($errors);

        return count($errors);
    }
}
