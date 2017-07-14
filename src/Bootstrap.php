<?php

declare(strict_types=1);

if (true === function_exists('xdebug_disable')) {
    xdebug_disable();
}

$autoloaders = ['vendor', '../..'];

foreach ($autoloaders as $autoloader) {
    $path =
        dirname(__DIR__) . DIRECTORY_SEPARATOR .
        $autoloader . DIRECTORY_SEPARATOR .
        'autoload.php';

    if (true === file_exists($path)) {
        require_once $path;

        break;
    }
}

use Hoa\File\Directory;
use Hoa\Protocol\Node;
use Hoa\Protocol\Protocol;

$output = sys_get_temp_dir() . DS . 'Kitab' . DS;
Directory::create($output);

$temporary = sys_get_temp_dir() . DS . 'Kitab.temp' . DS;
Directory::create($temporary);

$protocol = Protocol::getInstance();
$protocol[] = new Node(
    'Kitab',
    __DIR__ . DS,
    [
        new Node('Input', "\r" . getcwd() . DS),
        new Node('Output', "\r" . $output),
        new Node('Temporary', "\r" . $temporary)
    ]
);
