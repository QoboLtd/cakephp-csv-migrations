<?php
// Importer plugin configuration
return [
    'Importer' => [
        'path' => WWW_ROOT . 'uploads' . DS . 'imports' . DS,
        'max_attempts' => 3,
    ],
    'Translate' => [
        'pattern' => "/^%s__([a-z]{2})$/",
    ],
];
