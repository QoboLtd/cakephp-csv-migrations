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

namespace CsvMigrations\FieldHandlers\Provider\ValidationRules;

use Cake\ORM\TableRegistry;
use CsvMigrations\Model\Table\DblistsTable;
use Webmozart\Assert\Assert;

/**
 * DblistValidationRules
 *
 * This class provides the validation rules for the dblist field type.
 */
class DblistValidationRules extends AbstractValidationRules
{
    /**
     * {@inheritDoc}
     */
    public function provide($validator = null, array $options = [])
    {
        $validator = parent::provide($validator, $options);
        $validator->scalar($options['fieldDefinitions']->getName());

        $table = TableRegistry::getTableLocator()->get('CsvMigrations.Dblists');
        Assert::isInstanceOf($table, DblistsTable::class);
        $listOptions = $table->getOptions($options['fieldDefinitions']->getLimit());

        $validator->inList(
            $options['fieldDefinitions']->getName(),
            array_keys($listOptions)
        );

        return $validator;
    }
}
