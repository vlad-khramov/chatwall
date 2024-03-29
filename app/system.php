<?php
namespace App\System;


class Locator
{
    static private $config;

    static private $templatingSystem;

    static private $em;

    static private $timezone;

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
                'cache' => DEBUG?false:self::getConfig()->cache_dir,
            ));
        }
        return self::$templatingSystem;
    }

    public static function getEm() {
        if(!self::$em) {

            $cache = new \Doctrine\Common\Cache\ArrayCache;
            $config = new \Doctrine\ORM\Configuration;
            $config->setMetadataCacheImpl($cache);
            $driverImpl = $config->newDefaultAnnotationDriver(__DIR__);
            $config->setMetadataDriverImpl($driverImpl);
            $config->setQueryCacheImpl($cache);
            $config->setProxyDir(SYSTEM_DIR . '/proxies');
            $config->setProxyNamespace('App\Proxies');

            if (DEBUG) {
                $config->setAutoGenerateProxyClasses(true);
            } else {
                $config->setAutoGenerateProxyClasses(false);
            }

            self::$em = \Doctrine\ORM\EntityManager::create(self::getConfig()->db, $config);
        }
        return self::$em;
    }

    public static function  getTZ() {
        if(!self::$timezone) {
            self::$timezone = new \DateTimeZone(self::getConfig()->timezone);
        }
        return self::$timezone;
    }



}

class App {

    private function prepareRequest() {
        return array(
            'GET' => $_GET,
            'POST' => $_POST,
            'COOKIE' => $_COOKIE,
            'FILES' => $_FILES
        );
    }

    private function prepareResponse() {
        return array(
            'code' => '200',
            'content_type' => 'text/html',
            'text' => '',
            'cookie' => array()
        );
    }

    private function printHtml($response) {
        header('Content-type: '.$response['content_type']);
        header(' ', true, $response['code']);
        foreach($response['cookie'] as $name => $val) {
            setcookie($name, $val);
        }
        print $response['text'];

    }

    public function run() {
        date_default_timezone_set(Locator::getConfig()->timezone);

        set_error_handler(function($errno, $errstr, $errfile, $errline ){
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        $router = new Router(Locator::getConfig()->routes);

        $actionFullName = $router->getControllerName(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if($actionFullName) {
            $request = $this->prepareRequest();
            try {
                list($controllerName, $actionName) = explode('.', $actionFullName);
                $controllerName = "\\App\\Controllers\\". ucfirst(strtolower($controllerName));
                if(!class_exists($controllerName)) {
                    throw new \Exception('Controller not exists: ' . $controllerName);
                }
                $controller = new $controllerName($request);
                if(!method_exists($controller, $actionName)) {
                    throw new \Exception('Action not exists: ' . $actionName);
                }
                $result = $controller->$actionName();
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
                if(DEBUG) {
                    $response['text'] .= '<br>' . $e->getMessage() . '<br>' . $e->getTraceAsString();
                }
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

interface DeleteMarkable {

}


class DomainObject {

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value)
    {
        if (method_exists($this, $method = 'set' . ucfirst($name))) {
            $this->$method($value);
        }
        elseif (property_exists($this, $property = $name)) {
            $this->$property = $value;
        }
        else {
            throw new \Exception('Invalid property');
        }
    }

    public function __get($name)
    {
        if ( method_exists($this, $method = 'get' . ucfirst($name))) {
            return $this->$method();
        }
        elseif ( property_exists($this, $property = $name)) {
            return $this->$property;
        }
        else {
            throw new \Exception('Invalid property');
        }

    }

    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $this->__set($key, $value);
        }
        return $this;
    }

    public function asArray() {
        return get_object_vars($this);
    }

}

class Controller {
    protected $request;

    public function __construct($request) {
        $this->request = $request;
    }

    public function param($name, $default, $type="GET") {
        if(isset($this->request[$type][$name])) {
            return $this->request[$type][$name];
        } else {
            return $default;
        }
    }
}


class Manager {
    protected $class;
    protected $em;
    protected $deleteMarkable;

    public function __construct($class) {
        $this->class = $class;
        $this->em = Locator::getEm();
        $this->deleteMarkable = in_array('App\\System\\DeleteMarkable', class_implements($this->class));
    }

    public function get($pk) {
        if($this->deleteMarkable) {
            $dql = "SELECT c FROM {$this->class} c WHERE c.isDeleted is null and c.id={$pk}";
            $query = $this->em->createQuery($dql);
            try {
                return $query->getSingleResult();
            } catch(\Exception $e) {
                return null;
            }
        } else {
            return $this->em->find($this->class, $pk);
        }
    }

    public function save($object, $flush = false) {
        $this->em->persist($object);
        if($flush) {
            $this->em->flush();
        }
    }

    public function delete($object, $flush = false, $forceDelete = false) {
        if($this->deleteMarkable && !$forceDelete) {
            $object->isDeleted = new \DateTime();
            $this->em->persist($object);
        } else {
            $this->em->remove($object);
        }
        if($flush) {
            $this->em->flush();
        }
    }

    public function flush() {
        $this->em->flush();
    }


}