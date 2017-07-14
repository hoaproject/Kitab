<?php

declare(strict_types=1);

namespace Kitab\DocTest\Report\Cli\Fields;

use mageekguy\atoum\report\fields;

class Memory extends fields\test\memory\cli
{
    public function __toString()
    {
        return
            $this->prompt .
            sprintf(
                $this->locale->_('%1$s: %2$s.'),
                $this->titleColorizer->colorize($this->locale->_('Memory usage')),
                $this->memoryColorizer->colorize(
                    $this->value === null
                    ?
                    $this->locale->_('unknown')
                    :
                    sprintf(
                        $this->locale->_('%4.3f Kb'),
                        $this->value / 1024
                    )
                )
            ) . "\n";
    }
}
