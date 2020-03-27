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

use Cake\ORM\TableRegistry;
use CsvMigrations\FieldHandlers\Setting;
use CsvMigrations\Model\Table\DblistsTable;
use Webmozart\Assert\Assert;

/**
 * DblistRenderer
 *
 * Dblist renderer provides the functionality
 * for rendering database list inputs.
 */
class DblistRenderer extends AbstractRenderer
{
    /**
     * Provide
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $field = $this->config->getField();
        $table = $this->config->getTable();

        $fieldName = $table->aliasField($field);

        $list = $options['fieldDefinitions']->getListName();
        $table = TableRegistry::get('CsvMigrations.Dblists');
        Assert::isInstanceOf($table, DblistsTable::class);

        $params = [
            'field' => $field,
            'name' => $fieldName,
            'type' => 'select',
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'options' => array_merge(
                ['' => Setting::EMPTY_OPTION_LABEL()],
                $table->getOptions($list)
            ),
            'extraClasses' => (!empty($options['extraClasses']) ? implode(' ', $options['extraClasses']) : ''),
            'attributes' => empty($options['attributes']) ? [] : $options['attributes'],
            'help' => (!empty($options['help']) ? $options['help'] : ''),
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/DblistFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);
    }
}
