<?php
namespace CsvMigrations\View;

use BootstrapUI\View\UIView;

/**
 * App View class
 */
class AppView extends UIView
{
    public $layout = 'QoboAdminPanel.basic';

    /**
     * Initialization hook method.
     *
     * For e.g. use this method to load a helper for all views:
     * `$this->loadHelper('Html');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
    }
}
