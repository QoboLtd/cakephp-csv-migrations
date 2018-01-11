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
namespace CsvMigrations\FieldHandlers\Provider\RenderValue;

use CsvMigrations\FieldHandlers\Provider\AbstractProvider;
use InvalidArgumentException;

/**
 * AbstractRenderer
 *
 * Abstract renderer provides the default rendering
 * functionality.
 */
abstract class AbstractRenderer extends AbstractProvider
{
    /**
     * Provide rendered value
     *
     * @param mixed $data Data to use for provision
     * @param array $options Options to use for provision
     * @return mixed
     */
    public function provide($data = null, array $options = [])
    {
        $result = (string)$data;

        if (empty($result)) {
            return $result;
        }

        // Sanitize
        $result = filter_var($result, FILTER_SANITIZE_STRING);
        if ($result === false) {
            // If you find a case where FILTER_SANITIZE_STRING fails, add
            // a unit test to StringRendererTest/PlainRendererTest and
            // remove annotations here.
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException("Failed to sanitize string");
            // @codeCoverageIgnoreEnd
        }

        return $result;
    }
}
