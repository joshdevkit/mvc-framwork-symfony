<?php

require_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../bootstrap/app.php';

use App\Core\Application;


$app = new Application();

$app->run();
