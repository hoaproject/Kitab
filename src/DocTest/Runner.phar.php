<?php

require_once 'phar://' . KITAB_PHAR_NAME . '/vendor/atoum/atoum/scripts/runner.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Script' . DIRECTORY_SEPARATOR . 'Runner.php';

use Kitab\DocTest\Script\Runner;

// Disable autorun.
Runner::disableAutorun();

// Allocate the default runner.
$runner = new Runner(mageekguy\atoum\scripts\runner);

// Manually add the configuration file.
$runner->useConfigurationCallable(
    function ($script, $runner) {
        require_once 'phar://' . KITAB_PHAR_NAME . '/src/DocTest/.atoum.php';
    }
);

// Run atoum.
$runner->run();
