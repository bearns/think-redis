<?php

/*
|--------------------------------------------------------------------------
| Redis Databases
|--------------------------------------------------------------------------
|
| Redis is an open source, fast, and advanced key-value store that also
| provides a richer set of commands than a typical key-value systems
| such as APC or Memcached.
|
*/

return [
    
    'client' => 'predis',

    'default' => [
        'host' => ENV::get('REDIS_HOST', '127.0.0.1'),
        'password' => ENV::get('REDIS_PASSWORD', null),
        'port' => ENV::get('REDIS_PORT', 6379),
        'database' => 0,
    ],
];