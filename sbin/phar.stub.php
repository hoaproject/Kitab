#!/usr/bin/env php
<?php

define('KITAB_PHAR_NAME', 'Kitab.phar');
define('KITAB_PHAR_PATH', realpath($_SERVER['argv'][0]));

Phar::mapPhar(KITAB_PHAR_NAME);

require_once 'phar://' . KITAB_PHAR_NAME . '/src/Bin/Kitab.php';

__HALT_COMPILER();
