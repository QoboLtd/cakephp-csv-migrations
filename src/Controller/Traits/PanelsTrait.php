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
namespace CsvMigrations\Controller\Traits;

use CsvMigrations\Utility\Panel;

/**
 * This class provides assistance in using Panels.
 */
trait PanelsTrait
{
    /**
     * List of evaluated Panels.
     *
     * Returns the all the evaluated panels which are split into two
     * types success and fail.
     * Success type contains the panels have been evaluated with success
     * and vice verca for fail type.
     *
     * @see \CsvMigrations\Utility\Panel::evalExpression How the expression is evaluated.
     * @param mixed[] $config Table's config.
     * @param mixed[] $data to get the values for placeholders
     * @return mixed[] Evaluated panel list.
     */
    public function getPanels(array $config, array $data) : array
    {
        $result = ['success' => [], 'fail' => []];

        foreach (Panel::getPanelNames($config) as $name) {
            $panel = new Panel($name, $config);
            if (!empty($data) && $panel->evalExpression($data)) {
                $result['success'][] = $panel->getName();
            } else {
                $result['fail'][] = $panel->getName();
            }
        }

        return $result;
    }
}
