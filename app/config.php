<?php

return array(
    'db' => array(
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'user' => 'username',
        'password' => 'password',
        'dbname' => 'chatwall',

    ),
    'routes' => array(
        '/' => 'home.home',
        '/user/save' => 'home.userSave',
        '/messages/add' => 'home.messagesAdd',
        '/messages/delete' => 'home.messagesDelete',
        '/messages/like' => 'home.messagesLike',
        '/messages/getlast' => 'home.messagesGetLast',
        '/messages/getchanges' => 'home.messagesGetChanges',
    ),
    'cache_dir' => SYSTEM_DIR . '/cache',
    'templates_dir' => SYSTEM_DIR . '/templates',
    'media_dir' => SYSTEM_DIR . '/www/media',
    'timezone' => 'Europe/Moscow'
);