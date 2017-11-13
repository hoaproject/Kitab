<?php

use Kitab\Compiler\Target\DocTest;

$configuration = new DocTest\Configuration();
$configuration->autoloaderFile      = __DIR__ . '/vendor/autoload.php';
$configuration->concurrentProcesses = 4;

return $configuration;
