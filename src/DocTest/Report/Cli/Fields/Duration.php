<?php

declare(strict_types=1);

namespace Kitab\DocTest\Report\Cli\Fields;

use atoum\report\fields;

class Duration extends fields\test\duration\cli
{
    public function __toString()
    {
        return
            $this->prompt .
            sprintf(
                $this->locale->_('%1$s: %2$s.'),
                $this->titleColorizer->colorize($this->locale->_('Duration')),
                $this->durationColorizer->colorize(
                    null === $this->value
                        ? $this->locale->_('unknown')
                        : sprintf(
                            $this->locale->__('%4.6f second', '%4.6f seconds', $this->value),
                            $this->value
                        )
                )
            ) . "\n";
    }
}
