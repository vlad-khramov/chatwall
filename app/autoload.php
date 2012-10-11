<?php

spl_autoload_register(function($name) {
    $namespaces = explode('\\', $name);
    array_pop($namespaces);
    $namespaces = array_map('strtolower', $namespaces);

    if(file_exists(dirname(__DIR__) . '/' . implode('/', $namespaces) . '.php')) {
        require_once dirname(__DIR__) . '/' . implode('/', $namespaces) . '.php';
    }
});