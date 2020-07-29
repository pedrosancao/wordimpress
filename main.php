<?php

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    exit('Forbidden');
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once './vendor/autoload.php';
require_once './Site.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/.env' . (file_exists('./.env') ? '' : '.example'));

$site = new Site;
$generator = new PedroSancao\Wpsg\Generator($site);
$generator->prepare();
$generator->generate();
