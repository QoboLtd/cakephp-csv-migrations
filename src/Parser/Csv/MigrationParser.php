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
     * Parsing options
     */
    protected $options = [
        // Structure of the migration.csv file
        'structure' => [
            'name',
            'type',
            'required',
            'non-searchable',
            'unique',
        ],
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
     * @param array  $options   Parsing options
     * @param string $wrapField Field to use as the key
     * @return array
     */
    public function wrapFromPath($path, array $options = [], $wrapField = null)
    {
        $result = [];

        // Overwrite default wrap field
        if (!empty($wrapField)) {
            $this->wrapField = $wrapField;
        }

        // Overwrite defaults
        if (!empty($options)) {
            $this->options = $options;
        }

        // Make sure that wrap field is in the list of structure fields
        if (!in_array($this->wrapField, $this->options['structure'])) {
            throw new \InvalidArgumentException("Wrap field [" . $this->wrapField . "] is not in the structure");
        }

        $fields = $this->parseFromPath($path, $options, $this->wrapField);
        foreach ($fields as $field) {
            $result[$field[$this->wrapField]] = $field;
        }

        return $result;
    }
}
