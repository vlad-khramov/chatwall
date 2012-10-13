<?php

return array(
    'routes' => array(
        '/' => 'home.home',
        '/user/save' => 'home.userSave',
        '/messages/add' => 'home.messagesAdd',
        '/messages/delete' => 'home.messagesDelete',
        '/messages/getlast' => 'home.messagesGetLast',
    ),
    'cache_dir' => SYSTEM_DIR . '/cache',
    'templates_dir' => SYSTEM_DIR . '/templates',
    'db' => array(
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'user' => 'username',
        'password' => 'password',
        'dbname' => 'chatwall',

    ),
    'db2' => array(
        'driver' => 'pdo_sqlite',
        'path' => SYSTEM_DIR . '\db.sqlite'

    ),
);