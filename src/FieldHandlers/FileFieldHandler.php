<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use Cake\Core\Configure;
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
        if (empty($data)) {
            $result = $this->_renderInput($field);
        } else {
            $result = $this->_renderInputWithValue($field);
        }

        return $result;
    }

    /**
     * Renders new file input field with no value. Applicable for add action.
     *
     * @param  string $field name
     * @return string HTML input field.
     */
    protected function _renderInput($field)
    {
        $cakeView = new AppView();
        $uploadField = $cakeView->Form->file(
            'UploadDocuments.file.' . $field,
            ['class' => 'file']
        );
        $label = $cakeView->Form->label($field);

        return sprintf(self::WRAPPER, $label, $uploadField);
    }

    /**
     * Renders new file input field with value. Applicable for edit action.
     *
     * @param  string $field name
     * @return string HTML input field with data attribute.
     */
    protected function _renderInputWithValue($field)
    {
        $cakeView = new AppView();
        $cakeView->loadHelper(
            'Burzum/FileStorage.Storage',
            Configure::read('FileStorage.pathBuilderOptions')
        );
        $entity = $table->uploaddocuments->find()
            ->where(['id' => $data])
            ->first();
        $url = $cakeView->Storage->url($entity);
        //$img = $cakeView->Html->image($url);
        $uploadField = $cakeView->Form->file(
            'UploadDocuments.file.' . $field,
            ['data-upload-url' => $url]
        );
        $label = $cakeView->Form->label($field);

        return sprintf(self::WRAPPER, $label, $uploadField);
    }

    /**
     * {@inheritDoc}
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
                    $result = $this->_renderOtherFiles($entity);
                    break;
            }
        }

        return $result;
    }

    protected function _renderValueImage(Entity $entity)
    {
        return 'Not yet implemented';
    }

    /**
     * Creates a link to view the uploaded file.
     *
     * @param  Entity $entity Based on the entity the URL is being created by the plugin's helper.
     * @return string Link redirecting to the source of the uploaded file.
     */
    protected function _renderValueOtherFiles(Entity $entity)
    {
        $cakeView = new AppView();
        $cakeView->loadHelper(
            'Burzum/FileStorage.Storage',
            Configure::read('FileStorage.pathBuilderOptions')
        );
        $url = $cakeView->Storage->url($entity);
        return $cakeView->Html->link(
            __d('CsvMigrations', 'View File'),
            $cakeView->Url->build($url),
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
            $csvField->getNonSearchable()
        );

        return $dbFields;
    }
}
