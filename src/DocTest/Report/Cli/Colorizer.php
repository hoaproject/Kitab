<?php

declare(strict_types=1);

namespace Kitab\DocTest\Report\Cli;

use mageekguy\atoum;
use Hoa\Console;

class Colorizer extends atoum\cli\colorizer
{
    private $style;

    public function __construct($style)
    {
        $this->style = $style;
    }

    public function colorize($message)
    {
        return Console\Chrome\Text::colorize($message, $this->style);
    }
}
