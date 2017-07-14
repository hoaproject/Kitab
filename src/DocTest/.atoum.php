<?php

/**
 * Disable xdebug.
 */
if (true === function_exists('xdebug_disable')) {
    xdebug_disable();
}

/**
 * Our own report.
 */
$report = new Kitab\DocTest\Report\Cli\Cli();
$runner->addReport($report->addWriter(new atoum\writers\std\out()));
