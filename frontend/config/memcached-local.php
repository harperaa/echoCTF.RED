<?php
return [
    'class' => 'yii\caching\MemCache',
    'servers' => [
        [
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 60,
            'persistent'=>true,
        ],
    ],
    'useMemcached'=>false, // Change to true if you're using php-memcached
];
