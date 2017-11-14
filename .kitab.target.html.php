<?php

use Kitab\Compiler\Target\Html\Configuration;

$configuration = new Configuration();

$configuration->defaultNamespace = 'Kitab';
$configuration->logoURL          = 'https://raw.githubusercontent.com/hoaproject/Kitab/master/resource/logo.svg?sanitize=true';
$configuration->projectName      = 'Kitab';
$configuration->composerFile     = __DIR__ . '/composer.json';

return $configuration;
