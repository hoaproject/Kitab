<?php

declare(strict_types=1);

namespace Kitab\DocTest\Report\Cli\Fields;

use atoum\report\fields;

class Uncompleted extends fields\runner\tests\uncompleted\cli
{
    public function __toString()
    {
        $string = '';

        if (null !== $this->runner) {
            $uncompletedMethods      = $this->runner->getScore()->getUncompletedMethods();
            $sizeOfUncompletedMethod = sizeof($uncompletedMethods);

            if (0 < $sizeOfUncompletedMethod) {
                $string .=
                    $this->titlePrompt .
                    sprintf(
                        $this->locale->_('%s:'),
                        $this->titleColorizer->colorize(sprintf($this->locale->__('There is %d uncompleted test case', 'There are %d uncompleted test cases', $sizeOfUncompletedMethod), $sizeOfUncompletedMethod))
                    ) . "\n";

                foreach ($uncompletedMethods as $uncompletedMethod) {
                    $string .=
                        $this->methodPrompt .
                        sprintf(
                            $this->locale->_('%s:'),
                            $this->methodColorizer->colorize(sprintf('%s::%s() with exit code %d', $uncompletedMethod['class'], $uncompletedMethod['method'], $uncompletedMethod['exitCode']))
                        ) . "\n";

                    $lines   = explode(PHP_EOL, trim($uncompletedMethod['output']));
                    $string .= $this->outputPrompt . 'output(' . strlen($uncompletedMethod['output']) . ') "' . array_shift($lines);

                    foreach ($lines as $line) {
                        $string .= "\n" . $this->outputPrompt . $line;
                    }

                    $string .= '"' . "\n";
                }
            }
        }

        return $string;
    }
}
