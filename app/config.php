<?php

return array(
    'routes' => array(
        '/' => 'home.home',
        '/user/save' => 'home.user_save',
    ),
    'cache_dir' => SYSTEM_DIR . '/cache',
    'templates_dir' => SYSTEM_DIR . '/templates',
    'db' => array(
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'user' => 'username',
        'password' => 'password',
        'dbname' => 'chatwall',

    )
);