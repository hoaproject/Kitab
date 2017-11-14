<?php

use Kitab\Compiler\Target\DocTest\Configuration;

$configuration = new Configuration();

$configuration->autoloaderFile      = __DIR__ . '/vendor/autoload.php';
$configuration->concurrentProcesses = 4;

return $configuration;
