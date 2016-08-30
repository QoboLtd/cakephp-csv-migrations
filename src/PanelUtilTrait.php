<?php
namespace CsvMigrations;

use CsvMigrations\Panel;

/**
 * Utilities trait provides assistance in using Panels.
 */
trait PanelUtilTrait {

    /**
     * List of evaluated Panels.
     *
     * Returns the all the evaluated panels which are split into two
     * types success and fail.
     * Success type contains the panels have been evaluated with success
     * and vice verca for fail type.
     *
     * @see \CsvMigrations\Panel::evalExpression How the expression is evaluated.
     * @param  array  $config Table's config.
     * @param  array  $data to get the values for placeholders
     * @return array          Evaluated panel list.
     */
    public function getEvalPanels(array $config, array $data)
    {
        $result = ['success' => [], 'fail' => []];
        $panels = Panel::getPanelNames($config) ?: [];
        foreach ($panels as $name) {
            $panel = new Panel($name, $config);
            if ($panel->evalExpression($data)) {
                $result['success'][] = $panel->getName();
            } else {
                $result['fail'][] = $panel->getName();
            }
        }

        return $result;
    }
}
