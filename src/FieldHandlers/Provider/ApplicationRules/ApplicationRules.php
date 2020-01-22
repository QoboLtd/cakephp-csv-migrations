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
use Cake\Validation\Validator;
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

            $value = $entity->isDirty($field) ? $entity->getOriginal($field) : null;
            if (empty($value)) {
                return true;
            }

            $listOptions = (new $provider($this->config))->provide(
                $options['fieldDefinitions']->getLimit(),
                ['flatten' => true, 'filter' => true, 'value' => $value]
            );

            $validator = (new Validator())->inList($field, array_keys($listOptions));
            $payload = [$field => $entity->get($field)];
            $errors = $validator->errors($payload, $entity->isNew());
            $entity->getErrors($errors);

            return empty($errors);
        }, 'listRules');

        return $rules;
    }
}
