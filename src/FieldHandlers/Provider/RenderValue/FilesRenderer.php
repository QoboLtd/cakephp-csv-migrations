<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CsvMigrations\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FileUploadsUtils;

/**
 * FilesRenderer
 *
 * Files value as a linkable URL with icon
 */
class FilesRenderer extends AbstractRenderer
{
    /**
     * CSS Framework grid columns number
     */
    const GRID_COUNT = 12;

    /**
     * Limit of thumbnails to display
     */
    const THUMBNAIL_LIMIT = 6;

    /**
     * Default thumbnail file
     */
    const NO_THUMBNAIL_FILE = 'no-thumbnail.jpg';

    /**
     * Thumbnail html markup
     */
    const THUMBNAIL_HTML = '<div class="thumbnail">%s</div>';

    /**
     * CSS Framework row html markup
     */
    const GRID_ROW_HTML = '<div class="row">%s</div>';

    /**
     * CSS Framework row html markup
     */
    const GRID_COL_HTML = '<div class="col-xs-%d col-sm-%d col-md-%d col-lg-%d">%s</div>';

    /**
     * Icon extension
     */
    const ICON_EXTENSION = 'png';

    /**
     * Icon size
     */
    const ICON_SIZE = '48';

    /**
     * Provide rendered value
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $result = (string)$data;

        if (empty($result)) {
            return $result;
        }

        $table = $this->config->getTable();
        $field = $this->config->getField();

        $fileUploadsUtils = new FileUploadsUtils($table);

        $entities = $fileUploadsUtils->getFiles($table, $field, $data);

        if (!empty($entities)) {
            $result = $this->_filesHtml($entities, $fileUploadsUtils);
        }

        return $result;
    }

    /**
     * Generates file icons markup
     *
     * @param \Cake\ORM\ResultSet $entities File Entities
     * @param FileUploadsUtils $fileUploadsUtils fileUploadsUtils class object
     *
     * @return string
     */
    protected function _filesHtml($entities, FileUploadsUtils $fileUploadsUtils)
    {
        $result = null;
        $colWidth = static::GRID_COUNT / static::THUMBNAIL_LIMIT;
        $thumbnailUrl = 'CsvMigrations.thumbnails/' . static::NO_THUMBNAIL_FILE;

        $view = $this->config->getView();
        foreach ($entities as $k => $entity) {
            if ($k >= static::THUMBNAIL_LIMIT) {
                break;
            }

            $thumbnailUrl = $this->_getFileIconUrl($entity->extension);
            $thumbnail = sprintf(
                static::THUMBNAIL_HTML,
                $view->Html->image($thumbnailUrl, ['title' => $entity->filename])
            );

            $thumbnail = $view->Html->link($thumbnail, $entity->path, ['escape' => false, 'target' => '_blank']);

            $result .= sprintf(
                static::GRID_COL_HTML,
                $colWidth,
                $colWidth,
                $colWidth,
                $colWidth,
                $thumbnail
            );
        }

        $result = sprintf(static::GRID_ROW_HTML, $result);

        return $result;
    }

    /**
     * Get file icon url by file extension
     *
     * @param  string $extension File extension
     * @return string
     */
    protected function _getFileIconUrl($extension)
    {
        $file = strtolower($extension);
        $webroot = dirname(__FILE__) . DS . '..' . DS . '..' . DS . 'webroot' . DS;
        $filesDir = $webroot . 'img' . DS . 'icons' . DS . 'files' . DS . '48px' . DS;

        if (!file_exists($filesDir . $file . '.' . static::ICON_EXTENSION)) {
            $file = '_blank';
        }

        $view = $this->config->getView();

        return $view->Url->image(
            'CsvMigrations.icons/files/' . static::ICON_SIZE . 'px/' . $file . '.' . static::ICON_EXTENSION
        );
    }
}
