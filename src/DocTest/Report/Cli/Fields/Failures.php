<?php

declare(strict_types=1);

namespace Kitab\DocTest\Report\Cli\Fields;

use atoum\report\fields;

class Failures extends fields\runner\failures\cli
{
    public function __toString()
    {
        $string = '';

        if ($this->runner !== null) {
            $fails = $this->runner->getScore()->getFailAssertions();

            $numberOfFails = count($fails);

            if ($numberOfFails > 0) {
                $string .=
                    $this->titlePrompt .
                    sprintf(
                        $this->locale->_('%s:'),
                        $this->titleColorizer->colorize(sprintf($this->locale->__('There is %d failure', 'There are %d failures', $numberOfFails), $numberOfFails))
                    ) . "\n";

                foreach ($fails as $fail) {
                    $string .=
                        $this->methodPrompt .
                        sprintf(
                            '%s: ',
                            $this->methodColorizer->colorize($fail['class'] . '::' . $fail['method'] . '()')
                        ) .
                        $fail['fail'] . "\n\n";
                }
            }
        }

        return $string;
    }
}
