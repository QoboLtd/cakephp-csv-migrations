<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

class FileFieldHandler extends BaseFieldHandler
{
    /**
     * Field type
     */
    const FIELD_TYPE = 'uuid';

    /**
     * Defines the layout of the wrapper
     * Expects the label and the actual field.
     */
    const WRAPPER = '<div class="form-group">%s%s</div>';

    const DIV = '<div>%s&nbsp;%s</div>';
    /**
     * {@inheritDoc}
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $cakeView = new AppView();
        if (!$data) {
            $uploadField = $cakeView->Form->file('UploadDocuments.file.' . $field, ['class' => 'file']);
            $label = $cakeView->Form->label($field);
            $result = sprintf(self::WRAPPER, $label, $uploadField);
        } else {
            $cakeView->loadHelper('Burzum/FileStorage.Storage', [
                'pathBuilderOptions' => [
                    'pathPrefix' => '/uploads'
                ]
            ]);
            $entity = $table->uploaddocuments->find()
                ->where(['id' => $data])
                ->first();
            $url = $cakeView->Storage->url($entity);
            $img = $cakeView->Html->image($url);
            $uploadField = $cakeView->Form->file('UploadDocuments.file', ['data-upload-url' => $url]);
            $label = $cakeView->Form->label($field);
            $result = sprintf(self::WRAPPER, $label, $uploadField);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = __d('CsvMigration', 'No upload file');
        $cakeView = new AppView();
        $cakeView->loadHelper('Burzum/FileStorage.Storage', [
            'pathBuilderOptions' => [
                'pathPrefix' => '/uploads'
            ]
        ]);
        $entity = $table->uploaddocuments->find()
            ->where(['id' => $data])
            ->first();

        if (!$entity) {
            return $result;
        }
        $url = $cakeView->Storage->url($entity);
        $result = $cakeView->Html->link(
            __d('CsvMigrations', 'View File'),
            $cakeView->Url->build($url),
            ['target' => '_blank']
        );
        return $result;
    }

    /**
     * Method responsible for converting csv field instance to database field instance.
     *
     * @param  \CsvMigrations\FieldHandlers\CsvField $csvField CsvField instance
     * @return array list of DbField instances
     */
    public function fieldToDb(CsvField $csvField)
    {
        $dbFields[] = new DbField(
            $csvField->getName(),
            static::FIELD_TYPE,
            $csvField->getLimit(),
            $csvField->getRequired(),
            $csvField->getNonSearchable()
        );

        return $dbFields;
    }
}
