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
        if (isset($properties['config']['table']['icon'])) {
            $title = '<i class="menu-icon fa fa-' . $properties['config']['table']['icon'] . '"></i> ' . $title;
        }

        echo $this->Html->link(
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
