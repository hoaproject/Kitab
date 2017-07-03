<?php

declare(strict_types=1);

namespace Kitab\DocTest\Report\Cli\Fields;

use atoum\report\fields;

class Result extends fields\runner\result\cli
{
    public function __toString()
    {
        $string = "\n" . $this->prompt;

        if (null === $this->testNumber) {
            $string .= $this->locale->_('No test running.');
        } elseif (true === $this->success) {
            $string .= $this->successColorizer->colorize(
                sprintf(
                    $this->locale->_('Success (%s, %s, %s, %s, %s)!'),
                    sprintf($this->locale->__('%s documentation test suite', '%s documentation test suites', $this->testNumber), $this->testNumber),
                    sprintf($this->locale->__('%s/%s example', '%s/%s examples', $this->testMethodNumber), $this->testMethodNumber - $this->voidMethodNumber - $this->skippedMethodNumber, $this->testMethodNumber),
                    sprintf($this->locale->__('%s void example', '%s void examples', $this->voidMethodNumber), $this->voidMethodNumber),
                    sprintf($this->locale->__('%s skipped example', '%s skipped examples', $this->skippedMethodNumber), $this->skippedMethodNumber),
                    sprintf($this->locale->__('%s assertion', '%s assertions', $this->assertionNumber), $this->assertionNumber)
                )
            );
        } else {
            $string .= $this->failureColorizer->colorize(
                sprintf(
                    $this->locale->_('Failure (%s, %s, %s, %s, %s, %s, %s, %s)!'),
                    sprintf($this->locale->__('%s documentation test suite', '%s documentation test suites', $this->testNumber), $this->testNumber),
                    sprintf($this->locale->__('%s/%s example', '%s/%s examples', $this->testMethodNumber), $this->testMethodNumber - $this->voidMethodNumber - $this->skippedMethodNumber - $this->uncompletedMethodNumber, $this->testMethodNumber),
                    sprintf($this->locale->__('%s void example', '%s void examples', $this->voidMethodNumber), $this->voidMethodNumber),
                    sprintf($this->locale->__('%s skipped example', '%s skipped examples', $this->skippedMethodNumber), $this->skippedMethodNumber),
                    sprintf($this->locale->__('%s uncompleted example', '%s uncompleted example', $this->uncompletedMethodNumber), $this->uncompletedMethodNumber),
                    sprintf($this->locale->__('%s failure', '%s failures', $this->failNumber), $this->failNumber),
                    sprintf($this->locale->__('%s error', '%s errors', $this->errorNumber), $this->errorNumber),
                    sprintf($this->locale->__('%s exception', '%s exceptions', $this->exceptionNumber), $this->exceptionNumber)
                )
            );
        }

        return $string . "\n";
    }
}
