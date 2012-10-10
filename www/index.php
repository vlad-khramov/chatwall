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

//Twig
require_once '../vendor/autoload.php';

$loader = new \Twig_Loader_Filesystem('templates');
$twig = new \Twig_Environment($loader, array(
    'cache' => 'cache',
));
//end of twig

$router = new Router();

$router->match('/url', function($request) use ($twig) {
    //var_dump($request);
    print $twig->render('base.html', array('var' => 'sd'));
});

