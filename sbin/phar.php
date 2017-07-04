#!/usr/bin/env php -d phar.readonly=0
<?php

if (!isset($_SERVER['argv'][1])) {
    throw new RuntimeException(
        'The PHAR path is missing. Please, try with: '.
        '`' . implode(' ', $_SERVER['argv']) . ' /tmp/kitab.phar`.'
    );
}

$fileName = $_SERVER['argv'][1];

if (true === file_exists($fileName)) {
    throw new RuntimeException(
        'The PHAR ' . $fileName . ' already exist. Do not want to overwrite it.'
    );
}

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;

$_flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS;

$iterator = new AppendIterator();
$iterator->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . 'bin', $_flags)));
$iterator->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . 'src', $_flags)));
$iterator->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . 'vendor', $_flags)));
$iterator->append(new GlobIterator($root . '*.*'));

$phar = new Phar($fileName, 0, 'Kitab.phar');
$phar->buildFromIterator($iterator, $root);
$phar->setSignatureAlgorithm(Phar::SHA512);

$phar->setStub(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'phar.stub.php'));
