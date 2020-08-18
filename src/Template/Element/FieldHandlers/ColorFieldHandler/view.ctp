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
?>
<?php if (isset($options['renderAs']) && $options['renderAs'] === 'plain' ): ?>
    <?php echo $result; ?>
<?php else:?>
    <i style="background-color: <?php echo $result; ?>; display: inline-block; height: 16px; vertical-align: text-top; width: 16px;"></i>
    <?php echo $result; ?>
<?php endif; ?>
