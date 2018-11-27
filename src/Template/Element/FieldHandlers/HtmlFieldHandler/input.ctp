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

$attributes = isset($attributes) ? $attributes : [];

$attributes += [
    'type' => $type,
    'class' => 'tinymce' . ( (isset($extraClasses) && !empty($extraClasses)) ? ' ' . $extraClasses : null ),
    'label' => $label,
    'required' => (bool)$required,
    'value' => $value,
    'placeholder' => $placeholder,
];

echo $this->Form->control($name, $attributes);

use Cake\Core\Configure;

// load tinyMCE editor and elFinder file manager
echo $this->Html->script('Qobo/Utils./plugins/tinymce/tinymce.min', ['block' => 'scriptBottom']);

// initialize tinyMCE
echo $this->Html->scriptBlock(
    'var tinymce_init_config = ' . json_encode(Configure::read('TinyMCE')) . ';',
    ['block' => 'scriptBottom']
);

// @todo move this into a js file so we can avoid loading it more than once
$this->Html->scriptStart(['block' => 'scriptBottom']) ?>
(function ($) {
    /**
     * TinyMCE init
     */
    $(document).ready(function () {
        var config = {};
        if ('undefined' !== typeof tinymce_init_config) {
            config = tinymce_init_config;
        }

        tinyMCE.init(config);
    });

    // fix issue with link/image pop-up fields not working when tinymce is loaded within bootstrap modal
    // @link https://github.com/tinymce/tinymce/issues/782#issuecomment-151998981
    $(document).on('focusin', function (e) {
        if ($(event.target).closest('.mce-window').length) {
            e.stopImmediatePropagation();
        }
    });
})(jQuery);
<?= $this->Html->scriptEnd() ?>

