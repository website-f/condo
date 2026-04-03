<?php

use Illuminate\Support\Str;
use Pdo\Mysql;

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mysql2' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_SECOND', '127.0.0.1'),
            'port' => env('DB_PORT_SECOND', '3306'),
            'database' => env('DB_DATABASE_SECOND', 'forge'),
            'username' => env('DB_USERNAME_SECOND', 'root'),
            'password' => env('DB_PASSWORD_SECOND', ''),
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
        ],

        'mysqlIcp' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_ICP', '127.0.0.1'),
            'port' => env('DB_PORT_ICP', '3306'),
            'database' => env('DB_DATABASE_ICP', 'forge'),
            'username' => env('DB_USERNAME_ICP', 'root'),
            'password' => env('DB_PASSWORD_ICP', ''),
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
        ],

        'ipppasitesql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_IPP_PASITE', 'localhost'),
            'port' => env('DB_PORT_IPP_PASITE', '3306'),
            'database' => env('DB_DATABASE_IPP_PASITE', 'ipp_pasite'),
            'username' => env('DB_USERNAME_IPP_PASITE', 'root'),
            'password' => env('DB_PASSWORD_IPP_PASITE', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
        ],

        'condo' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_CONDO', 'localhost'),
            'port' => env('DB_PORT_CONDO', '3306'),
            'database' => env('DB_DATABASE_CONDO', 'wp_condo'),
            'username' => env('DB_USERNAME_CONDO', 'root'),
            'password' => env('DB_PASSWORD_CONDO', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => 'cd_',
            'prefix_indexes' => true,
            'strict' => false,
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
];
