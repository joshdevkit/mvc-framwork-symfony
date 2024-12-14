<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Cli\CliBuilder;

$args = $argv;
array_shift($args);

CliBuilder::handle($args);
