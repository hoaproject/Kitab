<?php

require_once 'phar://' . KITAB_PHAR_NAME . '/vendor/atoum/atoum/scripts/runner.php';

use mageekguy\atoum\scripts;

// Disable autorun.
scripts\runner::disableAutorun();

// Allocate the default runner.
$runner = new scripts\runner(scripts\runner);

// Manually add the configuration file.
$runner->useConfigurationCallable(
    function ($script, $runner) {
        require_once 'phar://' . KITAB_PHAR_NAME . '/src/DocTest/.atoum.php';
    }
);

// Run atoum.
$runner->run();
