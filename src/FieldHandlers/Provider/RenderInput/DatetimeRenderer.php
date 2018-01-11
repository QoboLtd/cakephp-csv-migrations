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

use Cake\I18n\Date;
use Cake\I18n\Time;

/**
 * DatetimeRenderer
 *
 * Datetime renderer provides the functionality
 * for rendering datetime inputs.
 */
class DatetimeRenderer extends AbstractRenderer
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
        if ($data instanceof Time) {
            $data = $data->i18nFormat('yyyy-MM-dd HH:mm');
        }
        if ($data instanceof Date) {
            $data = $data->i18nFormat('yyyy-MM-dd HH:mm');
        }

        $field = $this->config->getField();
        $table = $this->config->getTable();

        $fieldName = $table->aliasField($field);

        $params = [
            'field' => $field,
            'name' => $fieldName,
            'type' => 'datetimepicker',
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'extraClasses' => (!empty($options['extraClasses']) ? implode(' ', $options['extraClasses']) : ''),
            'attributes' => empty($options['attributes']) ? [] : $options['attributes'],
        ];

        $defaultElement = 'CsvMigrations.FieldHandlers/DatetimeFieldHandler/input';
        $element = empty($options['element']) ? $defaultElement : $options['element'];

        return $this->renderElement($element, $params);
    }
}
