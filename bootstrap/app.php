<?php

define('RESOURCE_VIEW_PATH', dirname(__DIR__) . "/resources/views");
define('RESOURCE_VIEW_LAYOUTS', dirname(__DIR__) . "/resources/views/layouts");
define('STORAGE_RESOURCES', dirname(__DIR__) . "/storage/framework/cache");
define('ROUTE_PATH', dirname(__DIR__) . '/routes/web.php');
define('BASE_PATH', dirname(__DIR__) . '/');


use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$config = [
    'host'   => $_ENV['DB_HOST'],
    'dbname' => $_ENV['DB_NAME'],
    'user'   => $_ENV['DB_USER'],
    'pass'   => $_ENV['DB_PASSWORD'],
];

$app = new \App\Core\Database($config);

return $app;
