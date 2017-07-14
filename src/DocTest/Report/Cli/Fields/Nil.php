<?php

declare(strict_types=1);

namespace Kitab\DocTest\Report\Cli\Fields;

use mageekguy\atoum\report\fields;

class Nil extends fields\runner\tests\blank\cli
{
    public function __toString()
    {
        $string = '';

        if (null !== $this->runner) {
            $voidMethods      = $this->runner->getScore()->getVoidMethods();
            $sizeOfVoidMethod = sizeof($voidMethods);

            if (0 < $sizeOfVoidMethod) {
                $string .=
                    $this->titlePrompt .
                    sprintf(
                        $this->locale->_('%s:'),
                        $this->titleColorizer->colorize(
                            sprintf(
                                $this->locale->__(
                                    'There is %d void test case',
                                    'There are %d void test cases',
                                    $sizeOfVoidMethod
                                ),
                                $sizeOfVoidMethod
                            )
                        )
                    ) . "\n";

                foreach ($voidMethods as $voidMethod) {
                    $string .=
                        $this->methodPrompt .
                        $this->methodColorizer->colorize(sprintf('%s::%s()', $voidMethod['class'], $voidMethod['method'])) .
                        "\n";
                }
            }
        }

        return $string;
    }
}
