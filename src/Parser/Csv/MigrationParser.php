<?php
namespace CsvMigrations\Parser\Csv;

/**
 * Migration CSV Parser
 *
 * This parser is useful for parsing migration CSV
 * files.
 *
 * @author Leonid Mamchenkov <l.mamchenkov@qobo.biz>
 */
class MigrationParser extends Parser
{
    /**
     * Structure of the migration.csv file
     */
    protected $structure = [
        'name',
        'type',
        'required',
        'non-searchable',
        'unique',
    ];
}
