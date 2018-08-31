<?php

foreach ($relatedProperties as $properties) {
    if (empty($properties)) {
        continue;
    }

    if (isset($options['renderAs']) && $options['renderAs'] === 'plain') {
        echo $properties['dispFieldVal'];
    } else {
        // generate related record(s) html link
        $title = $properties['dispFieldVal'];
        $beforeLink = null;

        // Special case for entities having an image_src like Users
        if (isset($properties['entity']['image_src'])) {
            $beforeLink = '<img alt="User Image" src="'. $properties['entity']['image_src'] .'" style="width: 20px; height: 20px;" class="img-circle"> ';
        } else if (isset($properties['config']['table']['icon'])) {
            $title = '<i class="menu-icon fa fa-' . $properties['config']['table']['icon'] . '"></i> ' . $title;
        }

        echo $beforeLink . $this->Html->link(
                $title,
                $this->Url->build([
                    'prefix' => false,
                    'plugin' => $properties['plugin'],
                    'controller' => $properties['controller'],
                    'action' => 'view',
                    $properties['id']
                ]),
                ['class' => 'label label-primary', 'escape' => false]
            );
    }
}
