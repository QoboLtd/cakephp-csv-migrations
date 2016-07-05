<?php
namespace CsvMigrations\View\Exception;

use Cake\Core\Exception\Exception;

/**
 * Used when a csv file cannot be found.
 *
 */
class MissingCsvException extends Exception
{

    protected $_messageTemplate = 'Csv file "%s" is missing.';
}
