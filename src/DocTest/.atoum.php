<?php

/**
 * Our own report.
 */
$report = new Kitab\DocTest\Report\Cli\Cli();
$runner->addReport($report->addWriter(new atoum\writers\std\out()));
