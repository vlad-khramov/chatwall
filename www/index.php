<?php

require_once '../vendor/autoload.php';
require_once '../app/autoload.php';

defined('DEBUG')
    || define('DEBUG', (getenv('DEBUG') ? getenv('DEBUG') : 0));

define('SYSTEM_DIR', dirname(__DIR__));

$app = new App\System\App();
$app->run();