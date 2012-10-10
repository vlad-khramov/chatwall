<?php
namespace System;

class Router {

    public function match($route, $callback) {
        if($route==parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) {

            $request = array(
                'GET' => $_GET,
                'POST' => $_POST,
                'COOKIE' => $_COOKIE
            );

            $callback($request);
        }

    }
}

$router = new Router();

$router->match('/url', function($request) {
    var_dump($request);
});
