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
namespace CsvMigrations\FieldHandlers\Provider\RenderInput;

use CsvMigrations\FieldHandlers\Provider\RenderValue\CountryRenderer as CountryValueRenderer;

/**
 * CountryRenderer
 *
 * Country renderer provides the functionality
 * for rendering list inputs.
 */
class CountryRenderer extends ListRenderer
{
    /**
     * Extend parent provide
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $selectListItems = $this->config->getProvider('selectOptions');
        $selectListItems = new $selectListItems($this->config);
        $listName = $options['fieldDefinitions']->getLimit();

        $selectOptions = $selectListItems->provide($listName, []);
        foreach ($selectOptions as $k => $v) {
            $selectOptions[$k] = sprintf(CountryValueRenderer::ICON_HTML, strtolower($k), $v);
        }

        $options['selectOptions'] = $selectOptions;
        $options['element'] = 'CsvMigrations.FieldHandlers/CountryFieldHandler/input';

        return parent::provide($data, $options);
    }
}
