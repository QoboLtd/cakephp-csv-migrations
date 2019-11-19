<?php

namespace CsvMigrations\Test\App\Model\Entity;

use Cake\ORM\Entity;

class Foo extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];
}
