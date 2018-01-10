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

use InvalidArgumentException;

/**
 * LinkRenderer
 *
 * Render value as a link to custom URL
 */
class LinkRenderer extends AbstractRenderer
{
    /**
     * Default link target
     */
    const TARGET = '_blank';

    /**
     * Provide rendered value
     *
     * Supported options:
     *
     * * linkTo     - string for link URL (use %s as placeholder).
     *                Default: none.
     * * linkTarget - string for link target (_self, _blank, etc).
     *                Default: '_blank'.
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

        if (!isset($options['linkTarget'])) {
            $options['linkTarget'] = static::TARGET;
        }

        if (!isset($options['linkTo'])) {
            $options['linkTo'] = '';
        }

        $options['linkTo'] = sprintf($options['linkTo'], rawurlencode($data));

        // Sanitize
        $options['linkTo'] = filter_var($options['linkTo'], FILTER_SANITIZE_URL);
        if ($options['linkTo'] === false) {
            // If you find a case where FILTER_SANITIZE_URL fails, add
            // a unit test to LinkRendererTest and remove annotations here.
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException("Failed to sanitize link URL");
            // @codeCoverageIgnoreEnd
        }
        $data = filter_var($data, FILTER_SANITIZE_STRING);
        if ($data === false) {
            // If you find a case where FILTER_SANITIZE_STRING fails, add
            // a unit test to LinkRendererTest and remove annotations here.
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException("Failed to sanitize string");
            // @codeCoverageIgnoreEnd
        }

        // Only link to URLs with schema, to avoid unpredictable behavior
        if (filter_var($options['linkTo'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) === false) {
            return $result;
        }

        $view = $this->config->getView();
        $result = $view->Html->link($data, $options['linkTo'], ['target' => $options['linkTarget']]);

        return $result;
    }
}
