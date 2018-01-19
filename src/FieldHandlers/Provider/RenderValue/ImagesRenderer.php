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

use Cake\Core\Configure;
use CsvMigrations\FileUploadsUtils;

/**
 * ImagesRenderer
 *
 * Images value as a linkable URL with icon
 */
class ImagesRenderer extends AbstractRenderer
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

        if (empty($options['imageSize'])) {
            $options['imageSize'] = Configure::read('FileStorage.defaultImageSize');
        }

        if (!empty($entities)) {
            $result = $this->_thumbnailsHtml($entities, $fileUploadsUtils, $options);
        }

        return $result;
    }

    /**
     * Generates thumbnails html markup.
     *
     * @param \Cake\ORM\ResultSet $entities File Entities
     * @param FileUploadsUtils $fileUploadsUtils fileUploadsUtils class object
     * @param array $options for default thumbs versions and other setttings
     *
     * @return string
     */
    protected function _thumbnailsHtml($entities, FileUploadsUtils $fileUploadsUtils, $options = [])
    {
        $result = null;
        $colWidth = static::GRID_COUNT / static::THUMBNAIL_LIMIT;
        $thumbnailUrl = 'CsvMigrations.thumbnails/' . static::NO_THUMBNAIL_FILE;

        $hashes = Configure::read('FileStorage.imageHashes.file_storage');
        $extensions = $fileUploadsUtils->getImgExtensions();

        foreach ($entities as $k => $entity) {
            if ($k >= static::THUMBNAIL_LIMIT) {
                break;
            }

            if (in_array($entity->extension, $extensions)) {
                $thumbnailUrl = $entity->path;

                if (isset($hashes[$options['imageSize']])) {
                    $version = $hashes[$options['imageSize']];

                    $exists = $this->_checkThumbnail($entity, $version, $fileUploadsUtils);

                    if ($exists) {
                        $path = dirname($entity->path) . '/' . basename($entity->path, $entity->extension);
                        $path .= $version . '.' . $entity->extension;
                        $thumbnailUrl = $path;
                    }
                }
            }

            $view = $this->config->getView();
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
     * Check if specified image version exists
     *
     * @param  \Cake\ORM\Entity $entity  Entity
     * @param  string           $version Image version
     * @param  \CsvMigrations\FileUploadsUtils $fileUploadsUtils fileUploadsUtils class object
     * @return bool
     */
    protected function _checkThumbnail($entity, $version, FileUploadsUtils $fileUploadsUtils)
    {
        // image version directory path
        $dir = realpath(WWW_ROOT . trim($entity->path, DS));
        $dir = dirname($dir) . DS . basename($dir, $entity->extension);
        $dir .= $version . '.' . $entity->extension;

        return file_exists($dir);
    }
}
