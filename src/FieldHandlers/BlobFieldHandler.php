<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\BaseSimpleFieldHandler;
use Phinx\Db\Adapter\MysqlAdapter;

class BlobFieldHandler extends BaseSimpleFieldHandler
{
    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'textarea';

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
    public function renderInput($data = '', array $options = [])
    {
        $data = $this->_getFieldValueFromData($data);
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }

        return parent::renderInput($data, $options);
    }

    /**
     * Render value as-is
     *
     * @todo Add support for encoding (base64, etc) via $options
     *
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($data, array $options = [])
    {
        $data = $this->_getFieldValueFromData($data);
        $result = $data;
        if (is_resource($data)) {
            $result = stream_get_contents($data);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function renderSearchInput(array $options = [])
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
