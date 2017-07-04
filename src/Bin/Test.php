<?php

declare(strict_types=1);

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2017, Hoa community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Kitab\Bin;

use Hoa\Console;
use Hoa\Console\Processus;
use Hoa\Event;
use Hoa\File\Temporary\Temporary;
use Hoa\Protocol\Node;
use Hoa\Protocol\Protocol;
use Kitab\Compiler\Compiler;
use Kitab\Compiler\Target\DocTest\DocTest;
use Kitab\Finder;

class Test extends Console\Dispatcher\Kit
{
    /**
     * Options description.
     *
     * @var array
     */
    protected $options = [
        ['with-composer',    Console\GetOption::OPTIONAL_ARGUMENT, 'c'],
        ['output-directory', Console\GetOption::REQUIRED_ARGUMENT, 'o'],
        ['verbose',          Console\GetOption::NO_ARGUMENT,       'v'],
        ['help',             Console\GetOption::NO_ARGUMENT,       'h'],
        ['help',             Console\GetOption::NO_ARGUMENT,       '?']
    ];



    /**
     * The entry method.
     *
     * @return  int
     */
    public function run()
    {
        $composerFile    = null;
        $outputDirectory = Temporary::getTemporaryDirectory() . DS . 'Kitab.test.output';
        $directoryToScan = null;
        $verbose         = false;

        while (false !== $c = $this->getOption($v)) {
            switch ($c) {
                case 'c':
                    if (false === is_string($v)) {
                        $composerFile = './composer.json';
                    } else {
                        $composerFile = $v;
                    }

                    break;

                case 'o':
                    $outputDirectory = $v;

                    break;

                case 'v':
                    $verbose = $v;

                    break;

                case 'h':
                case '?':
                    return $this->usage();

                case '__ambiguous':
                    $this->resolveOptionAmbiguity($v);

                    break;
            }
        }

        if (null !== $composerFile) {
            if (false === file_exists($composerFile)) {
                throw new RuntimeException('Composer file `' . $composerFile . '` is not found.');
            }

            $composerFileContent = json_decode(file_get_contents($composerFile), true);

            if (isset($composerFileContent['autoload']) &&
                isset($composerFileContent['autoload']['psr-4'])) {
                $protocolInput     = Protocol::getInstance()['Kitab']['Input'];
                $composerDirectory = dirname($composerFile);

                foreach ($composerFileContent['autoload']['psr-4'] as $psrNamespaces => $psrDirectory) {
                    $latestNode = $protocolInput;

                    foreach (explode('\\', trim($psrNamespaces, '\\')) as $psrNamespace) {
                        if (!isset($latestNode[$psrNamespace])) {
                            $latestNode[$psrNamespace] = new Node($psrNamespace);
                        }

                        $latestNode = $latestNode[$psrNamespace];
                    }

                    $latestNode->setReach("\r" . $composerDirectory . DS . $psrDirectory . DS);
                }
            }
        }

        Protocol::getInstance()['Kitab']['Output']->setReach("\r" . $outputDirectory . DS);

        $this->parser->listInputs($directoryToScan);

        if (empty($directoryToScan)) {
            throw new \RuntimeException('Directory to scan must not be empty.');
        }

        if (true === $verbose) {
            echo
                'Directory to scan: ', $directoryToScan, "\n",
                'Output directory : ', $outputDirectory, "\n";
        }

        $finder = new Finder();
        $finder->in($directoryToScan);

        if (is_dir($outputDirectory)) {
            $since = time() - filemtime($outputDirectory);
            $finder->modified('since ' . $since . ' seconds');
        }

        $target = new DocTest();

        $compiler = new Compiler();
        $compiler->compile($finder, $target);

        $command =
            dirname(dirname(__DIR__)) . DS .
            'vendor' . DS .
            'atoum' . DS .
            'atoum' . DS .
            'bin' . DS .
            'atoum';
        $autoloaderFile =
            dirname(dirname(__DIR__)) . DS .
            'vendor' . DS .
            'autoload.php';

        if (false === file_exists($command)) {
            throw new \RuntimeException(
                'Cannot locate `atoum` to execute the generated test suites.'
            );
        }

        $command .=
            ' --configurations ' .
                dirname(__DIR__) . DS . 'DocTest' . DS . '.atoum.php' .
            ' --autoloader-file ' .
                $autoloaderFile .
            ' --force-terminal' .
            ' --max-children-number 4' .
            ' --directories ' .
                $outputDirectory;

        $processus = new Processus($command, null, null, getcwd(), $_SERVER);
        $processus->on(
            'input',
            function (Event\Bucket $bucket) {
                return false;
            }
        );
        $processus->on(
            'output',
            function (Event\Bucket $bucket) {
                echo $bucket->getData()['line'], "\n";

                return;
            }
        );
        $processus->on(
            'stop',
            function (Event\Bucket $bucket) {
                // Wait on sub-processes to stop.
                sleep(1);
                exit($bucket->getSource()->getExitCode());
            }
        );
        $processus->run();

        return;
    }

    /**
     * Print help.
     *
     * @return  int
     */
    public function usage()
    {
        echo
            'Usage   : test <options> directory-to-scan', "\n",
            'Options :', "\n",
            $this->makeUsageOptionsList([
                'c'    => 'Use a specific Composer file to get PSR-4 mappings ' .
                          '(default: `./composer.json` if enabled).',
                'o'    => 'Directory that will receive the generated documentation.',
                'v'    => 'Be verbose.',
                'help' => 'This help.'
            ]);

        return;
    }
}
