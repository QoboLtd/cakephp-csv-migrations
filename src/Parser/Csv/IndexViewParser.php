<?php
namespace CsvMigrations\Parser\Csv;

/**
 * Index View CSV Parser
 *
 * This parser is useful for parsing index view CSV
 * files (index.csv).
 *
 * @author Leonid Mamchenkov <l.mamchenkov@qobo.biz>
 */
class IndexViewParser extends Parser
{
    /**
     * Structure of the index.csv file
     */
    protected $structure = [
        'field',
    ];
}
