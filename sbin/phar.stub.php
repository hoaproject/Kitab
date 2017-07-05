#!/usr/bin/env php
<?php

define('KITAB_PHAR_NAME', 'Kitab.phar');
define('KITAB_PHAR_PATH', realpath($_SERVER['argv'][0]));

Phar::mapPhar(KITAB_PHAR_NAME);

require_once 'phar://' . KITAB_PHAR_NAME . '/src/Bootstrap.php';

if (isset($_SERVER['argv'][1]) && 'atoum' === $_SERVER['argv'][1]) {
    // Clean `$_SERVER` for atoum.
    unset($_SERVER['argv'][1]);

    // Manually add the configuration file.
    mageekguy\atoum\scripts\runner::addConfigurationCallable(
        function ($script, $runner) {
            require_once 'phar://' . KITAB_PHAR_NAME . '/src/DocTest/.atoum.php';
        }
    );

    // Run atoum.
    require_once 'phar://' . KITAB_PHAR_NAME . '/vendor/atoum/atoum/scripts/runner.php';
} else {
    require_once 'phar://' . KITAB_PHAR_NAME . '/src/Bin/Kitab.php';
}

__HALT_COMPILER();
