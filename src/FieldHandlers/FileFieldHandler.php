<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

class FileFieldHandler extends BaseFieldHandler
{

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
            $uploadField = $cakeView->Form->file('UploadDocuments.file', ['class' => 'file']);
            $label = $cakeView->Form->label($field);
            $result = sprintf(self::WRAPPER, $label, $uploadField);
        } else {
            $label = $cakeView->Form->label($field);
            $cakeView->loadHelper('Burzum/FileStorage.Storage', [
                'pathBuilderOptions' => [
                    'pathPrefix' => '/uploads'
                ]
            ]);
            $entity = $table->uploaddocuments->find()
                ->where(['id' => $data])
                ->first();
            $url = $cakeView->Storage->url($entity);
            $viewLink = $cakeView->Html->link(
                __d('CsvMigrations', 'View File'),
                $cakeView->Url->build($url),
                ['target' => '_blank']
            );
            $unlinkCta = $cakeView->Form->postLink(
                '',
                ['action' => 'unlinkUpload', $data],
                [
                    'confirm' => __d('CsvMigration', 'Are you sure you want to unlink?'),
                    'title' => __d('CsvMigration', 'Unlink upload'),
                    'class' => 'btn btn-default glyphicon glyphicon-trash'
                ]
            );
            $div = sprintf(self::DIV, $viewLink, $unlinkCta);
            $result = sprintf(self::WRAPPER, $label, $div);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = __d('CsvMigration', 'No upload file');
        if (is_null($data)) {
            return $result;
        }
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
}
