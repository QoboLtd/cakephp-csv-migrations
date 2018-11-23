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
namespace CsvMigrations\FieldHandlers\Provider\FieldValue;

use Cake\Datasource\EntityInterface;
use CsvMigrations\FieldHandlers\Provider\AbstractProvider;
use Psr\Http\Message\RequestInterface;

/**
 * MixedFieldValue
 *
 * MixedFieldValue provides the functionality
 * of looking for field value in a variety of
 * (mixed) data.
 */
class MixedFieldValue extends AbstractProvider
{
    /**
     * Provide field value
     *
     * @param mixed $data Data to look for field value in (Request, Entity, etc)
     * @param array $options Options to use for provision
     * @return mixed Field value
     */
    public function provide($data = null, array $options = [])
    {
        if (empty($data) && ! empty($options['entity'])) {
            $data = $options['entity'];
        }

        if ($data instanceof EntityInterface) {
            return $this->provideFromEntity($data, $this->config->getField());
        }

        if ($data instanceof RequestInterface) {
            return $this->provideFromRequest($data, $this->config->getField());
        }

        return $data;
    }

    /**
     * Get field value from Entity
     *
     * Use $entity->$field if available.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to look for field value in
     * @param string $field Field name
     * @return mixed Field value
     */
    protected function provideFromEntity(EntityInterface $entity, string $field)
    {
        return $entity->get($field);
    }

    /**
     * Get field value from Request
     *
     * Use $request->data[$field] if available.
     *
     * @param \Psr\Http\Message\RequestInterface $request Request to look for field value in
     * @param string $field Field name
     * @return string Field value
     */
    protected function provideFromRequest(RequestInterface $request, string $field) : string
    {
        /** @var \Cake\Http\ServerRequest */
        $request = $request;

        $data = $request->getData();

        return is_array($data) && array_key_exists($field, $data) ? $data[$field] : '';
    }
}
