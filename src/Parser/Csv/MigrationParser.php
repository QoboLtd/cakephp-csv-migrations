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

    /**
     * Field to use for wrapping into associative array
     */
    protected $wrapField = 'name';

    /**
     * Wrap parsed fields into associative array
     *
     * Generic CSV parser returns an index array of fields.
     * For migrations however it useful to have an associative
     * array, where the key is the name (or other identifier of
     * the field) and the value is the array of the field
     * details.
     *
     * @throws \InvalidArgumentException If $wrapField is not in the structure
     * @param string $path      Path to file
     * @param array  $structure Structure of the file
     * @param string $wrapField Field to use as the key
     * @return array
     */
    public function wrapFromPath($path, array $structure = [], $wrapField = null)
    {
        $result = [];

        // Overwrite default wrap field
        if (!empty($wrapField)) {
            $this->wrapField = $wrapField;
        }

        // Make sure that wrap field is in the list of structure fields
        if (!in_array($this->wrapField, $this->structure)) {
            throw new \InvalidArgumentException("Wrap field [" . $this->wrapField . "] is not in the structure");
        }

        $fields = $this->parseFromPath($path, $structure, $this->wrapField);
        foreach ($fields as $field) {
            $result[$field[$this->wrapField]] = $field;
        }

        return $result;
    }
}
