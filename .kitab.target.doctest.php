<?php

use Kitab\Compiler\Target\DocTest\Configuration;

$configuration = new Configuration();

$configuration->concurrentProcesses = 4;

return $configuration;
