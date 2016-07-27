<?php
namespace CsvMigrations\FieldHandlers;

use Cake\Core\Configure;
use Cake\Utility\Hash;
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

    /**
     * {@inheritDoc}
     * @todo To avoid confusion: data param is not used because
     * it has no value. We do not store anything in the file field on DB.
     *
     * In this case, it renders the output based on the given value of data.
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $entity = Hash::get($options, 'entity');
        if (empty($entity)) {
            $result = $this->_renderInputWithoutData($table, $field, $options);
        } else {
            $result = $this->_renderInputWithData($table, $field, $options);
        }

        return $result;
    }

    /**
     * Renders new file input field with no value. Applicable for add action.
     *
     * @param  Table $table Table
     * @param  string $field Field
     * @param  array $options Options
     * @return string HTML input field.
     */
    protected function _renderInputWithoutData($table, $field, $options)
    {
        $uploadField = $this->cakeView->Form->file(
            $this->_getFieldName($table, $field, $options),
            ['multiple' => true]
        );
        $label = $this->cakeView->Form->label($field);

        return sprintf(self::WRAPPER, $label, $uploadField);
    }

    /**
     * Renders new file input field with value. Applicable for edit action.
     *
     * @param  Table $table Table
     * @param  string $field Field
     * @param  array $options Options
     * @return string HTML input field with data attribute.
     */
    protected function _renderInputWithData($table, $field, $options)
    {
        $file = [];
        $entity = Hash::get($options, 'entity');
        $document = $table->find()
            ->contain(['DocumentIdCrmReFiles' => ['FileIdFileStorageFileStorage']])
            ->where(['id' => $entity->get('id')])
            ->first()
            ->toArray();
        $fileWrappers = Hash::get($document, 'document_id_crm_re_files');
        foreach ($fileWrappers as $fw) {
            $fileStorage = Hash::get($fw, 'file_id_file_storage_file_storage');
            $path = Hash::get($fileStorage, 'path');
            $id = Hash::get($fileStorage, 'id');
            $files[] = ['id' => $id, 'path' => $path];
        }
        if (empty($files)) {
            return $this->_renderInputWithoutData($table, $field, $options);
        }

        $uploadField = $this->cakeView->Form->file(
            $this->_getFieldName($table, $field, $options),
            [
                'multiple' => true,
                'data-document-id' => $entity->get('id'),
                'data-files' => json_encode($files),
            ]
        );
        $label = $this->cakeView->Form->label($field);

        return sprintf(self::WRAPPER, $label, $uploadField);
    }

    /**
     * {@inheritDoc}
     * In this case, it renders img tag or anchor to view the upload files.
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = __d('CsvMigration', 'No upload file');
        if (empty($data)) {
            return $result;
        } else {
            $entity = $table->uploaddocuments->find()
                ->where(['id' => $data])
                ->first();
            if (!$entity) {
                return $result;
            }
            $mime = $entity->get('mime_type');
            list($type) = explode('/', $mime);
            switch ($type) {
                case 'image':
                    $result = $this->_renderValueImage($entity);
                    break;
                default:
                    $result = $this->_renderValueOtherFiles($entity);
                    break;
            }
        }

        return $result;
    }

    /**
     * Displays the uploaded img.
     *
     * @param  object $entity FileStorage entity
     * @return string HTML img tag
     */
    protected function _renderValueImage($entity)
    {
        $this->cakeView->loadHelper(
            'Burzum/FileStorage.Storage',
            Configure::read('FileStorage.pathBuilderOptions')
        );
        $url = $this->cakeView->Storage->url($entity);

        return $this->cakeView->Html->image($this->cakeView->Url->build($url), ['class' => 'img-responsive']);
    }

    /**
     * Creates a link to view the uploaded file.
     *
     * @param  Entity $entity Based on the entity the URL is being created by the plugin's helper.
     * @return string Link redirecting to the source of the uploaded file.
     */
    protected function _renderValueOtherFiles($entity)
    {
        $this->cakeView->loadHelper(
            'Burzum/FileStorage.Storage',
            Configure::read('FileStorage.pathBuilderOptions')
        );
        $url = $this->cakeView->Storage->url($entity);

        return $this->cakeView->Html->link(
            __d('CsvMigrations', 'View File'),
            $this->cakeView->Url->build($url),
            ['target' => '_blank']
        );
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
            $csvField->getNonSearchable(),
            $csvField->getUnique()
        );

        return $dbFields;
    }
}
