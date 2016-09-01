<?php
namespace CsvMigrations\Parser\Csv;

/**
 * List CSV Parser
 *
 * This parser is useful for parsing list CSV
 * files.
 *
 * @author Leonid Mamchenkov <l.mamchenkov@qobo.biz>
 */
class ListParser extends Parser
{
    /**
     * Structure of the some_list.csv file
     */
    protected $structure = [
        'value',
        'label',
        'inactive',
    ];
}
