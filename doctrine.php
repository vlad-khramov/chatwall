<?php
use \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use \Doctrine\ORM\Tools\Console\ConsoleRunner;
use \Symfony\Component\Console\Helper\HelperSet;

require_once 'vendor/autoload.php';
require_once 'app/autoload.php';

define('SYSTEM_DIR', dirname(__DIR__));
define('DEBUG', false);

$em = \App\System\Locator::getEm();

$helperSet = new HelperSet(array(
    'db' => new ConnectionHelper($em->getConnection()),
    'em' => new EntityManagerHelper($em)
));

ConsoleRunner::run($helperSet);