<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\ListFieldHandler;

abstract class BaseCombinedFieldHandler extends ListFieldHandler
{
    /**
     * Input(s) wrapper html markup
     */
    const WRAPPER_HTML = '%s<div class="row">%s</div>';

    /**
     * Input field html markup
     */
    const INPUT_HTML = '<div class="col-xs-6 col-lg-4">%s</div>';

    /**
     * Combined fields
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * Constructor
     */
    public function __construct($cakeView = null)
    {
        parent::__construct($cakeView);

        $this->_setCombinedFields();
    }

    /**
     * Set combined fields
     *
     * @return void
     */
    abstract protected function _setCombinedFields();

    /**
     * {@inheritDoc}
     *
     * @todo refactor to use base fields as renderValue() does now
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $label = $this->cakeView->Form->label($field);

        $inputs = [];
        foreach ($this->_fields as $suffix => $preOptions) {
            $options['fieldDefinitions']->setType($preOptions['handler']::DB_FIELD_TYPE);
            $options['label'] = null;
            $fieldName = $field . '_' . $suffix;

            $data = '';
            if (isset($options['entity'])) {
                $data = $options['entity']->{$fieldName};
            }

            $handler = new $preOptions['handler'];

            $inputs[] = sprintf(static::INPUT_HTML, $handler->renderInput($table, $fieldName, $data, $options));
        }

        return sprintf(static::WRAPPER_HTML, $label, implode('', $inputs));
    }

    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = [];
        foreach ($this->_fields as $suffix => $fieldOptions) {
            $fieldName = $field . '_' . $suffix;
            if (isset($options['entity'])) {
                $data = $options['entity']->{$fieldName};
            }
            $handler = new $fieldOptions['handler'];
            $result[] = $handler->renderValue($table, $fieldName, $data, $options);
        }

        $result = implode('&nbsp;', $result);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function fieldToDb(CsvField $csvField)
    {
        $dbFields = [];
        foreach ($this->_fields as $suffix => $options) {
            $handler = new $options['handler'];
            $subField = clone $csvField;
            $subField->setName($csvField->getName() . '_' . $suffix);
            if (isset($options['limit'])) {
                $subField->setLimit($options['limit']);
            }

            $dbFields = array_merge($dbFields, $handler->fieldToDb($subField));
        }

        return $dbFields;
    }
}
