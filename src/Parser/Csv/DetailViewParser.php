<?php
namespace CsvMigrations\Parser\Csv;

/**
 * Detail View CSV Parser
 *
 * This parser is useful for parsing detail view CSV
 * files, such as add.csv, edit.csv, and view.csv.
 *
 * @author Leonid Mamchenkov <l.mamchenkov@qobo.biz>
 */
class DetailViewParser extends Parser
{
    /**
     * Structure of the view.csv file
     */
    protected $structure = [
        'panel',
        'first',
        'second',
    ];
}
