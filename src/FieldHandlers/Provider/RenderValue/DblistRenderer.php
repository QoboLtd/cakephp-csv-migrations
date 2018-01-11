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

use InvalidArgumentException;

/**
 * DblistRenderer
 *
 * Render value as database list item
 */
class DblistRenderer extends AbstractRenderer
{
    /**
     * Provide rendered value
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $result = '';
        $data = (string)$data;

        // No known list name, so render value as safe string
        if (empty($options['listName'])) {
            return parent::provide($data, $options);
        }

        $listName = (string)$options['listName'];
        $view = $this->config->getView();
        $result = (string)$view->cell('CsvMigrations.Dblist::renderValue', [$data, $listName])->render('renderValue');

        return $result;
    }
}
