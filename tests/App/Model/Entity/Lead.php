<?php
namespace CsvMigrations\Test\App\Model\Entity;

use Cake\ORM\Entity;

class Lead extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];
}
