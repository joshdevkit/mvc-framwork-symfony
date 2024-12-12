<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Database;
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$config = [
    'host'   => $_ENV['DB_HOST'],
    'dbname' => $_ENV['DB_NAME'],
    'user'   => $_ENV['DB_USER'],
    'pass'   => $_ENV['DB_PASSWORD'],
];

$db = new Database($config);
$conn = $db->connect();

$app = new Application();

$app->boot();
