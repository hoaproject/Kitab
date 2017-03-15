<?php

require_once
    dirname(__DIR__) . DIRECTORY_SEPARATOR .
    'vendor' . DIRECTORY_SEPARATOR .
    'autoload.php';

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
        new Node('Output', "\r" . $output)
    ]
);
