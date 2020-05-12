<?php

namespace CsvMigrations\Test\App\Model\Entity;

use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

class Article extends Entity
{
    use TranslateTrait;

    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];
}
