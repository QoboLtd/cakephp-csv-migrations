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
namespace CsvMigrations\FieldHandlers\Provider\ApplicationRules;

use Cake\Core\App;
use Cake\Validation\Validation;
use InvalidArgumentException;

/**
 * Application Rules
 */
class ApplicationRules extends AbstractApplicationRules
{
    /**
     * Provide select options
     *
     * @param mixed $rules Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($rules = null, array $options = [])
    {
        $provider = $this->config->getProvider('selectOptions');

        $rules->add(function ($entity) use ($options, $provider) {
            $field = $options['fieldDefinitions']->getName();

            $oldValue = (!$entity->isNew() && $entity->isDirty($field)) ? $entity->getOriginal($field) : null;
            $newValue = $entity->get($field);

            if (empty($oldValue) && !(bool)$options['fieldDefinitions']->getRequired()) {
                return true;
            }

            $listOptions = (new $provider($this->config))->provide(
                $options['fieldDefinitions']->getLimit(),
                ['flatten' => true, 'filter' => true, 'value' => $oldValue]
            );

            $validation = Validation::inList($newValue, array_keys($listOptions));
            if (!$validation) {
                $entity->setErrors([$field => ["inList" => "The provided value is invalid"]]);
            }

            return $validation;
        }, 'listRoles');

        return $rules;
    }
}
