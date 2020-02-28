<?php

use Cake\Core\Configure;
?>
<ul class="list-inline">
    <?php foreach ($associationList as $id => $title) { ?>
        <li>
            <?php
            if (isset($options['renderAs']) && $options['renderAs'] === 'plain') {
                echo $title;
            } else {
                // Special case for entities having an image_src
                if (Configure::read('Theme.prependAvatars', true) && !empty($relatedProperties['entity']['image_src'])) {
                    $title = '<img alt="Thumbnail" src="' . $relatedProperties['entity']['image_src'] . '" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;' . $title;
                } elseif (!empty($relatedProperties['config']['table']['icon'])) {
                    $title = '<i class="menu-icon fa fa-' . $relatedProperties['config']['table']['icon'] . '"></i>&nbsp;&nbsp;' . $title;
                }

                echo $this->Html->link(
                    $title,
                    $this->Url->build([
                        'prefix' => false,
                        'plugin' => $relatedProperties['plugin'],
                        'controller' => $relatedProperties['controller'],
                        'action' => 'view',
                        $id
                    ]),
                    ['class' => 'btn btn-primary btn-xs', 'escape' => false]
                );
            }
            ?>
        </li>
    <?php } ?>
</ul>
