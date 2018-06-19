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
use CsvMigrations\Utility\FileUpload;

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

        $fileUpload = new FileUpload($table);

        $entities = $fileUpload->getFiles($table, $field, $data);

        $params = [
            'entities' => $entities,
            'hashes' => (array)Configure::read('FileStorage.imageHashes.file_storage'),
            'extensions' => $fileUpload->getImgExtensions(),
            'imageSize' => empty($options['imageSize']) ?
                Configure::read('FileStorage.defaultImageSize') :
                $options['imageSize']
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/ImagesFieldHandler/value';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);

        if (empty($options['imageSize'])) {
            $options['imageSize'] = Configure::read('FileStorage.defaultImageSize');
        }

        if (!empty($entities)) {
            $result = $this->_thumbnailsHtml($entities, $fileUpload, $options);
        }

        return $result;
    }

    /**
     * Generates thumbnails html markup.
     *
     * @param \Cake\ORM\ResultSet $entities File Entities
     * @param \CsvMigrations\Utility\FileUpload $fileUpload FileUpload instance
     * @param array $options for default thumbs versions and other setttings
     *
     * @return string
     */
    protected function _thumbnailsHtml($entities, FileUpload $fileUpload, $options = [])
    {
        $result = null;
        $colWidth = static::GRID_COUNT / static::THUMBNAIL_LIMIT;
        $thumbnailUrl = 'Qobo/Utils.thumbnails/no-thumbnail.jpg';

        $hashes = Configure::read('FileStorage.imageHashes.file_storage');
        $extensions = $fileUpload->getImgExtensions();

        foreach ($entities as $k => $entity) {
            if ($k >= static::THUMBNAIL_LIMIT) {
                break;
            }

            if (in_array($entity->extension, $extensions)) {
                $thumbnailUrl = $entity->path;

                if (isset($hashes[$options['imageSize']])) {
                    $version = $hashes[$options['imageSize']];

                    $exists = $this->_checkThumbnail($entity, $version);

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
     * @param \Cake\ORM\Entity $entity Entity
     * @param string $version Image version
     * @return bool
     */
    protected function _checkThumbnail($entity, $version)
    {
        // image version directory path
        $dir = realpath(WWW_ROOT . trim($entity->path, DS));
        $dir = dirname($dir) . DS . basename($dir, $entity->extension);
        $dir .= $version . '.' . $entity->extension;

        return file_exists($dir);
    }
}
