<?php

namespace CsvMigrations;

interface HasFieldsInterface
{

    /**
     * Get fields from CSV file
     *
     * This method gets all fields defined in the CSV and returns
     * them as an associative array.
     * @param mixed[] $stubFields Stub fields
     * @return mixed[] Associative array of fields and their definitions
    */
    public function getFieldsDefinitions(array $stubFields = []) : array;
}
