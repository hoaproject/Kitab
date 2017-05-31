<?php

$autoloaders = ['vendor', '../..'];

foreach ($autoloaders as $autoloader) {
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR .
        $autoloader . DIRECTORY_SEPARATOR . 'autoload.php';

    if (file_exists($path)) {
        require_once $path;

        break;
    }
}

use Hoa\File\Directory;
use Hoa\Protocol\Node;
use Hoa\Protocol\Protocol;

$output = sys_get_temp_dir() . DS . 'Kitab' . DS;

Directory::create($output);

$protocol = Protocol::getInstance();
$protocol[] = new Node(
    'Kitab',
    __DIR__ . DS,
    [
        new Node('Input', "\r" . getcwd() . DS),
        new Node('Output', "\r" . $output)
    ]
);
