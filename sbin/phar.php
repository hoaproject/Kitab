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

$iterators = new AppendIterator();
$iterators->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . 'bin', $_flags)));
$iterators->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . 'src', $_flags)));
$iterators->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . 'vendor', $_flags)));
$iterators->append(new GlobIterator($root . '*.*'));

$iterator = new CallbackFilterIterator(
    $iterators,
    function ($file) use ($root) {
        $relativePath = substr($file->getPathname(), strlen($root));

        return 0 === preg_match(
            '#^(' .
                'vendor/atoum/atoum/bin|' .
                'vendor/atoum/atoum/tests|' .
                'vendor/hoa/[^/]+/Bin|' .
                'vendor/hoa/[^/]+/Documentation|' .
                'vendor/hoa/[^/]+/Test|' .
                'vendor/league/commonmark/bin|' .
                'vendor/nikic/php-parser/bin|' .
                'vendor/nikic/php-parser/doc|' .
                'vendor/nikic/php-parser/grammar|' .
                'vendor/nikic/php-parser/test_old|' .
                'vendor/nikic/php-parser/test|' .
                '.*\.md$' .
            ')#',
            $relativePath
        );
    }
);

$phar = new Phar($fileName, 0, 'Kitab.phar');
$phar->buildFromIterator($iterator, $root);
$phar->setSignatureAlgorithm(Phar::SHA512);

$phar->setStub(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'phar.stub.php'));
