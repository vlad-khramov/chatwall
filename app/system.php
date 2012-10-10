<?php
namespace App\System;


class Locator
{
    static private $config;

    static private $templatingSystem;

    public static function getConfig() {
        if(!self::$config) {
            if(!$configPath=getenv('CONFIG_PATH')) {
                $configPath = __DIR__ . '/config.php';
            }
            self::$config = (object) require_once $configPath;
        }
        return self::$config;
    }

    public static function getTS() {
        if(!self::$templatingSystem) {
            $loader = new \Twig_Loader_Filesystem(self::getConfig()->templates_dir);
            self::$templatingSystem = new \Twig_Environment($loader, array(
                'cache' => self::getConfig()->cache_dir,
            ));
        }
        return self::$templatingSystem;
    }


}

class App {

    private function prepareRequest() {
        return array(
            'GET' => $_GET,
            'POST' => $_POST,
            'COOKIE' => $_COOKIE
        );
    }

    private function prepareResponse() {
        return array(
            'code' => '200',
            'content_type' => 'text/html',
            'text' => ''
        );
    }

    private function printHtml($response) {
        header('Content-type: '.$response['content_type']);
        header(' ', true, $response['code']);
        print $response['text'];

    }

    public function run() {
        $router = new Router(Locator::getConfig()->routes);

        $actionFullName = $router->getControllerName(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if($actionFullName) {
            $request = $this->prepareRequest();
            try {
                list($controllerName, $actionName) = explode('.', $actionFullName);
                $controllerName = "\\App\\Controllers\\". ucfirst(strtolower($controllerName));
                $controller = new $controllerName();
                $result = $controller->$actionName($request);
                if(is_array($result)) {
                    $response = $result;
                } else {
                    $response['text'] = $result;
                }
            } catch(\Exception $e) {
                $response = array(
                    'code' => 500,
                    'text' => 'Application error'
                );
            }
        } else {
            $response = array(
                'code' => 404,
                'text' => 'Not found'
            );
        }

        $this->printHtml(array_merge($this->prepareResponse(), $response));

    }
}

class Router {

    private $routes;

    public function __construct(array $routes) {
        $this->routes = $routes;
    }

    public function getControllerName($path) {
        if(isset($this->routes[$path])) {
            return $this->routes[$path];
        } else {
            return false;
        }
    }
}