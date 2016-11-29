<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseFieldHandler;
use Phinx\Db\Adapter\MysqlAdapter;

class BlobFieldHandler extends BaseFieldHandler
{
    /**
     * {@inheritDoc}
     * In addtion, it sets the limit to Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG
     */
    public function fieldToDb(CsvField $csvField)
    {
        $dbFields[] = new DbField(
            $csvField->getName(),
            $csvField->getType(),
            MysqlAdapter::BLOB_LONG,
            $csvField->getRequired(),
            $csvField->getNonSearchable(),
            $csvField->getUnique()
        );

        return $dbFields;
    }

    /**
     * {@inheritDoc}
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }

        return parent::renderInput($table, $field, $data, $options);
    }

    /**
     * Render value as-is
     *
     * @todo Add support for encoding (base64, etc) via $options
     *
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = $data;
        if (is_resource($data)) {
            $result = stream_get_contents($data);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function renderSearchInput($table, $field, array $options = [])
    {
        $content = $this->cakeView->Form->input('{{name}}', [
            'value' => '{{value}}',
            'type' => 'text',
            'label' => false
        ]);

        return [
            'content' => $content
        ];
    }
}
