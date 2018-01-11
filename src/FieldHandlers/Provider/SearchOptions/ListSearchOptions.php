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
namespace CsvMigrations\FieldHandlers\Provider\SearchOptions;

/**
 * ListSearchOptions
 *
 * List search options
 */
class ListSearchOptions extends AbstractSearchOptions
{
    /**
     * Provide search options
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $template = $this->getBasicTemplate('text');
        $defaultOptions = $this->getDefaultOptions($data, $options);
        $defaultOptions['input'] = ['content' => $template];

        $result[$this->config->getField()] = $defaultOptions;

        $selectListItems = $this->config->getProvider('selectOptions');
        $selectListItems = new $selectListItems($this->config);
        $listName = $options['fieldDefinitions']->getLimit();
        $listOptions = [];

        $selectOptions = ['' => ' -- Please choose -- '];
        $selectOptions += $selectListItems->provide($listName, $listOptions);

        $options['listItems'] = $selectOptions;

        $view = $this->config->getView();
        $content = $view->Form->select('{{name}}', $selectOptions, [
            'class' => 'form-control',
            'label' => false
        ]);

        $result[$this->config->getField()]['input'] = [
            'content' => $content
        ];

        return $result;
    }
}
