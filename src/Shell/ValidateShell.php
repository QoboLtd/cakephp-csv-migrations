<?php
namespace CsvMigrations\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use CsvMigrations\Parser\Csv\MigrationParser;
use CsvMigrations\Parser\Csv\ViewParser;
use CsvMigrations\Parser\Ini\Parser;
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
        $errorsCount += $this->_checkConfigOptions($modules);

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
     * Check if the given module is valid
     *
     * @param string $module Module name to check
     * @param array $validModules List of valid modules
     * @return bool True if module is valid, false otherwise
     */
    protected function _isValidModule($module, $validModules)
    {
        $result = false;

        if (in_array($module, $validModules)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Check if the given field is valid for given module
     *
     * If valid fields are not available from the migration
     * we will assume that the field is valid.
     *
     * @param string $module Module to check in
     * @param string $field Field to check
     * @return bool True if field is valid, false otherwise
     */
    protected function _isValidModuleField($module, $field)
    {
        $result = false;

        $moduleFields = [];
        try {
            $pathFinder = new MigrationPathFinder;
            $path = $pathFinder->find($module);
            $parser = new MigrationParser;
            $moduleFields = $parser->parseFromPath($path);
        } catch (\Exception $e) {
            // We already report issues with migration in _checkMigrationPresence()
        }

        // If we couldn't get the migration, we cannot verify if the
        // field is valid or not.  To avoid unnecessary fails, we
        // assume that it's valid.
        if (empty($moduleFields)) {
            $result = true;

            return $result;
        }

        foreach ($moduleFields as $moduleField) {
            if ($field == $moduleField['name']) {
                $result = true;
                break;
            }
        }

        return $result;
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
            // Warn if the module is missing standard views
            if ($viewCounter < count($views)) {
                $this->out('<warning>' . (int)$viewCounter . ' views</warning> ... ', 0);
            } else {
                $this->out('<info>' . (int)$viewCounter . ' views</info> ... ', 0);
            }
            $this->out('<info>' . (int)$viewCounter . ' views</info> ... ', 0);
            $result = empty($moduleErrors) ? '<success>OK</success>' : '<error>FAIL</error>';
            $this->out($result);
            $errors = array_merge($errors, $moduleErrors);
        }
        $this->_printCheckStatus($errors);

        return count($errors);
    }

    /**
     * Check configuration options for each module
     *
     * @param array $modules List of modules to check
     * @return int Count of errors found
     */
    protected function _checkConfigOptions(array $modules = [])
    {
        $errors = [];

        $this->out('Checking configuration options:', 2);
        foreach ($modules as $module => $path) {
            $moduleErrors = [];
            $this->out(' - ' . $module . ' ... ', 0);
            $config = null;
            try {
                $pathFinder = new ConfigPathFinder;
                $path = $pathFinder->find($module);
                $parser = new Parser;
                $config = $parser->parseFromPath($path);
            } catch (\Exception $e) {
                // We've already reported this problem in _checkConfigPresence();
            }

            // Check configuration options
            if ($config) {
                // [table] section
                if (!empty($config['table'])) {
                    // 'display_field' key is optional, but must contain valid field if specified
                    if (!empty($config['table']['display_field'])) {
                        if (!$this->_isValidModuleField($module, $config['table']['display_field'])) {
                            $moduleErrors[] = $module . " config [table] section references unknown field '" . $config['table']['display_field'] . "' in 'display_field' key";
                        }
                    }
                    // 'typeahead_fields' key is optional, but must contain valid fields if specified
                    if (!empty($config['table']['typeahead_fields'])) {
                        $typeaheadFields = explode(',', trim($config['table']['typeahead_fields']));
                        foreach ($typeaheadFields as $typeaheadField) {
                            if (!$this->_isValidModuleField($module, $typeaheadField)) {
                                $moduleErrors[] = $module . " config [table] section references unknown field '" . $typeaheadField . "' in 'typeahead_fields' key";
                            }
                        }
                    }
                    // 'lookup_fields' key is optional, but must contain valid fields if specified
                    if (!empty($config['table']['lookup_fields'])) {
                        $lookupFields = explode(',', $config['table']['lookup_fields']);
                        foreach ($lookupFields as $lookupField) {
                            if (!$this->_isValidModuleField($module, $lookupField)) {
                                $moduleErrors[] = $module . " config [table] section references unknown field '" . $lookupField . "' in 'lookup_fields' key";
                            }
                        }
                    }
                }
                // [manyToMany] section
                if (!empty($config['manyToMany'])) {
                    // 'module' key is required and must contain valid modules
                    if (empty($config['manyToMany']['modules'])) {
                        $moduleErrors[] = $module . " config [manyToMany] section is missing 'modules' key";
                    } else {
                        $manyToManyModules = explode(',', $config['manyToMany']['modules']);
                        foreach ($manyToManyModules as $manyToManyModule) {
                            if (!$this->_isValidModule($manyToManyModule, array_keys($modules))) {
                                $moduleErrors[] = $module . " config [manyToMany] section references unknown module '$manyToManyModule' in 'modules' key";
                            }
                        }
                    }
                }
                // [conversion] section
                if (!empty($config['conversion'])) {
                    // 'module' key is required and must contain valid modules
                    if (empty($config['conversion']['modules'])) {
                        $moduleErrors[] = $module . " config [conversion] section is missing 'modules' key";
                    } else {
                        $conversionModules = explode(',', $config['conversion']['modules']);
                        foreach ($conversionModules as $conversionModule) {
                            if (!$this->_isValidModule($conversionModule, array_keys($modules))) {
                                $moduleErrors[] = $module . " config [conversion] section references unknown module '$conversionModule' in 'modules' key";
                            }
                        }
                    }
                    // 'inherit' key is optional, but must contain valid modules if defined
                    if (!empty($config['conversion']['inherit'])) {
                        $inheritModules = explode(',', $config['conversion']['inherit']);
                        foreach ($inheritModules as $inheritModule) {
                            if (!$this->_isValidModule($inheritModule, array_keys($modules))) {
                                $moduleErrors[] = $module . " config [conversion] section references unknown module '$inheritModule' in 'inherit' key";
                            }
                        }
                    }
                    // 'field' key is optional, but must contain valid field and 'value' if defined
                    if (!empty($config['conversion']['field'])) {
                        // 'field' key is optional, but must contain valid field is specified
                        if (!$this->_isValidModuleField($module, $config['conversion']['field'])) {
                            $moduleErrors[] = $module . " config [conversion] section references unknown field '" . $config['conversion']['field'] . "' in 'field' key";
                        }
                        // 'value' key must be set
                        if (!isset($config['conversion']['value'])) {
                            $moduleErrors[] = $module . " config [conversion] section references 'field' but does not set a 'value' key";
                        }
                    }
                }
            }

            $result = empty($moduleErrors) ? '<success>OK</success>' : '<error>FAIL</error>';
            $this->out($result);
            $errors = array_merge($errors, $moduleErrors);
        }
        $this->_printCheckStatus($errors);

        return count($errors);
    }
}
