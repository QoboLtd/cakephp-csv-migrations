<?php

use Cake\Core\Configure;

foreach ($relatedProperties as $properties) {
    if (empty($properties)) {
        continue;
    }

    // generate related record(s) html link
    $title = $properties['dispFieldVal'];

    // Special case for entities having an image_src like Users
    if (Configure::read('Theme.prependAvatars', true) && !empty($properties['entity']['image_src'])) {
        $title = '<img alt="Thumbnail" src="' . $properties['entity']['image_src'] . '" style="width: 20px; height: 20px;" class="img-circle">&nbsp;&nbsp;' . $title;
    } elseif (!empty($properties['config']['table']['icon'])) {
        $title = '<i class="menu-icon fa fa-' . $properties['config']['table']['icon'] . '"></i>&nbsp;&nbsp;' . $title;
    }

    if (isset($options['renderAs']) && $options['renderAs'] === 'plain') {
        echo $properties['dispFieldVal'];
    }
    elseif (isset($options['renderAs']) && $options['renderAs'] === 'badge') {
        echo $this->Html->div('btn btn-default btn-xs', $title);
    }
    else {
        echo $this->Html->link(
            $title,
            $this->Url->build([
                'prefix' => false,
                'plugin' => $properties['plugin'],
                'controller' => $properties['controller'],
                'action' => 'view',
                $properties['id']
            ]),
            ['class' => 'btn btn-primary btn-xs', 'escape' => false]
        );
    }
}
