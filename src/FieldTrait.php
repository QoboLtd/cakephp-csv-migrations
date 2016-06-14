<?php
namespace CsvMigrations;

trait FieldTrait
{
    /**
     * Method that returns table's unique constrained fields.
     *
     * @return array unique fields
     */
    public function getUniqueFields(Table $table = null)
    {
        if (is_null($table)) {
            $table = $this;
        }

        $schema = $table->schema();

        $result = [];
        foreach ($schema->constraints() as $name) {
            $constraint = $schema->constraint($name);
            if ('unique' !== $constraint['type']) {
                continue;
            }

            foreach ($constraint['columns'] as $column) {
                if (!in_array($column, $result)) {
                    array_push($result, $column);
                }
            }
        }

        return $result;
    }
}
