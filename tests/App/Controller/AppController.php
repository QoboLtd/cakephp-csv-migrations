<?php

namespace CsvMigrations\Test\App\Controller;

use Cake\Controller\Controller;
use CsvMigrations\Controller\Traits\ImportTrait;

class AppController extends Controller
{
    use ImportTrait;

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Auth', [
            'authenticate' => ['Form'],
        ]);

        $this->loadComponent('Flash');
        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
    }
}
