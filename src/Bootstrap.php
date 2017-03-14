<?php

require_once
    dirname(__DIR__) . DIRECTORY_SEPARATOR .
    'vendor' . DIRECTORY_SEPARATOR .
    'autoload.php';

use Hoa\Protocol\Node;
use Hoa\Protocol\Protocol;

$output = sys_get_temp_dir() . DS . 'Kitab' . DS;

if (false === is_dir($output)) {
    mkdir($output, 0755, true);
}

$protocol = Protocol::getInstance();
$protocol[] = new Node(
    'Kitab',
    __DIR__ . DS,
    [
        new Node('Output', "\r" . $output)
    ]
);
