<?php
namespace CsvMigrations\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;

class UpgradeShell extends Shell
{

    /**
     * Module directories
     *
     * @var array $moduleDirs Directories each module must have
     */
    protected $moduleDirs = [
        'db',
        'config',
        'lists',
        'views',
    ];

    /**
     * Shell entry point
     */
    public function main()
    {
        $src = $this->args[0];
        if (DIRECTORY_SEPARATOR != substr($src, -1, 1)) {
            $src .= DIRECTORY_SEPARATOR;
        }

        $this->out("Upgrading file paths in [$src]");
        try {
            $this->upgrade($src);
        } catch (\Exception $e) {
            $this->abort($e->getMessage());
        }
        $this->out("All done");
    }

    /**
     * Get option parser
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');
        $parser->description('Upgrade CsvMigration file paths');
        $parser->addArgument('src', [
            'help' => 'Path to CsvMigrations files (e.g.: config/CsvMigrations)',
            'required' => true,
        ]);

        return $parser;
    }

    /**
     * Validate source folder
     *
     * @throws \InvalidArgumentException when $src is empty
     * @throws \RuntimeException when $src does not exist or is not a directory
     * @param string $src Path to source folder
     */
    protected function validateSource($src)
    {
        if (empty($src)) {
            throw new \InvalidArgumentException("Source path cannot be empty");
        }
        if (!file_exists($src)) {
            throw new \RuntimeException("Source path [$src] does not exist");
        }
        if (!is_dir($src)) {
            throw new \RuntimeException("Source path [$src] is not a directory");
        }
    }

    /**
     * Move parent folder
     *
     * Move config/CsvMigrations to config/Modules
     *
     * @throws \RuntimeException when the move failed
     * @param string $src Source folder path
     * @param string $dst Destination path
     */
    protected function moveParentFolder($src, $dst)
    {
        // Move the main folder
        $this->out("Renaming $src to $dst");
        $result = rename($src, $dst);
        if (!$result) {
            throw new \RuntimeException("Failed to rename [$src] to [$dst]");
        }
    }

    /**
     * Create module folders
     *
     * @throws \RuntimeException when folder creation fails
     * @param string $dst Destination folder path
     * @param string $module Module name
     */
    protected function createModuleFolders($dst, $module)
    {
        // Prepend destination and module to module directories
        $dirs = array_map(function ($a) use ($dst, $module) {
            return $dst . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $a;
        }, $this->moduleDirs);

        // Add module's parent directory to the top of the list
        array_unshift($dirs, $dst . DIRECTORY_SEPARATOR . $module);

        foreach ($dirs as $dir) {
            // Skip if directory already exists
            if (file_exists($dir) && is_dir($dir)) {
                continue;
            }
            $result = mkdir($dir);
            if (!$result) {
                throw new \RuntimeException("Failed to create [$dir]");
            }
        }
    }

    /**
     * Remove given folder
     *
     * @throws \RuntimeException if removal fails
     * @param string $dst Path to folder to remove
     */
    protected function removeFolder($dst)
    {
        $result = rmdir($dst);
        if (!$result) {
            throw new \RuntimeException("Failed to remove [$dst]");
        }
    }

    /**
     * Move files from given to source to destination
     *
     * @throws \RuntimeException when failed to move file
     * @param string $src Path to source folder
     * @param string $dst Path to destination folder
     * @param array $files Optional list of files to move (all, if empty)
     */
    protected function moveFiles($src, $dst, array $files = [])
    {
        if (empty($files)) {
            $files = new \DirectoryIterator($src);
        }
        foreach ($files as $file) {
            // Convert SplFileInfo objects to file names
            if (is_object($file)) {
                $file = $file->getFilename();
            }
            // Skip dot files
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            $srcFile = $src . DIRECTORY_SEPARATOR . $file;
            $dstFile = $dst . DIRECTORY_SEPARATOR . $file;
            $result = rename($srcFile, $dstFile);
            if (!$result) {
                throw new \RuntimeException("Failed moving [$srcFile] to [$dstFile]");
            }
        }
    }

    /**
     * Upgrade given path
     *
     * @param string $src Path to folder to upgrade
     */
    protected function upgrade($src)
    {
        $this->validateSource($src);

        $dst = dirname($src) . DIRECTORY_SEPARATOR . 'Modules';

        $this->moveParentFolder($src, $dst);
        $this->createModuleFolders($dst, 'Common');

        // Move all lists into Common module
        $this->out("Moving all lists into Common module");
        $srcDir = $dst . DIRECTORY_SEPARATOR . 'lists';
        $dstDir = $dst . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'lists';
        $this->moveFiles($srcDir, $dstDir);
        $this->removeFolder($srcDir);

        // Move all views files
        $this->out("Moving all views files");
        $viewsDir = $dst . DIRECTORY_SEPARATOR . 'views';
        $dir = new \DirectoryIterator($viewsDir);
        foreach ($dir as $moduleDir) {
            if ($moduleDir->isDot()) {
                continue;
            }
            $moduleName = $moduleDir->getFilename();
            $this->createModuleFolders($dst, $moduleName);

            $srcDir = $viewsDir . DIRECTORY_SEPARATOR . $moduleName;
            $dstDir = $dst . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'views';
            $this->moveFiles($srcDir, $dstDir);
            $this->removeFolder($srcDir);
        }
        $this->removeFolder($viewsDir);

        // Move all migration files
        $this->out("Moving all migration files");
        $migrationsDir = $dst . DIRECTORY_SEPARATOR . 'migrations';
        $dir = new \DirectoryIterator($migrationsDir);
        foreach ($dir as $moduleDir) {
            if ($moduleDir->isDot()) {
                continue;
            }

            $moduleName = $moduleDir->getFilename();
            $this->createModuleFolders($dst, $moduleName);

            // migration.csv goes into db/ folder
            $srcDir = $migrationsDir . DIRECTORY_SEPARATOR . $moduleName;
            $dstDir = $dst . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'db';
            $this->moveFiles($srcDir, $dstDir, ['migration.csv']);

            // everything else goes into config/ folder
            $dstDir = $dst . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'config';
            $this->moveFiles($srcDir, $dstDir);

            $this->removeFolder($srcDir);
        }
        $this->removeFolder($migrationsDir);
    }
}
