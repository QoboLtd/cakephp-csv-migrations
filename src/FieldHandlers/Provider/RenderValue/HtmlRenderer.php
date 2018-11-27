<?php

namespace CsvMigrations\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Provider\AbstractProvider;

class HtmlRenderer extends AbstractProvider
{
    /**
     * Provide
     *
     * @param mixed $data Data to use for provision
     * @param mixed[] $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        // TODO: need to handle this better, type-casting to string will produce errors
        return (string)$data;
    }
}
