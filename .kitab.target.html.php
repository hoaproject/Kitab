<?php

use Kitab\Compiler\Target\Html\Configuration;

$configuration = new Configuration();
$configuration->defaultNamespace = 'Kitab';
$configuration->logoURL          = 'https://static.hoa-project.net/Image/Hoa.png';
$configuration->projectName      = 'Kitab';
$configuration->composerFile     = __DIR__ . '/composer.json';

return $configuration;
