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

namespace CsvMigrations\View\Helper;

use Cake\View\Helper\HtmlHelper as Helper;

class MyHtmlHelper extends Helper
{
    public $helpers = ['Html'];

    /**
     * Template for help tooltip
     *
     * @param string $message Help message
     * @return string
     */
    public function help(string $message): string
    {
        return '<span data-toggle="tooltip" title="" class="badge bg-blue" data-placement="auto right" data-original-title="' . __($message) . '">?</span>';
    }
}
