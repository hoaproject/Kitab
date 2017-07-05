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
use Hoa\File;
use Hoa\File\Temporary\Temporary;
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
        ['autoloader',       Console\GetOption::REQUIRED_ARGUMENT, 'l'],
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
        $outputDirectory = null;
        $autoloader      = null;
        $directoryToScan = null;
        $verbose         = false;

        while (false !== $c = $this->getOption($v)) {
            switch ($c) {
                case 'o':
                    $outputDirectory = $v;

                    break;

                case 'l':
                    $autoloader = $v;

                    if (false === file_exists($autoloader)) {
                        throw new \RuntimeException('Autoloader file `' . $autoloader . '` does not exist.');
                    }

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

        $this->parser->listInputs($directoryToScan);

        if (empty($directoryToScan)) {
            throw new \RuntimeException(
                'Directory to scan must not be empty.' . "\n" .
                'Retry with ' . '`' . implode(' ', $_SERVER['argv']) . ' src` ' .
                'to test the documentation inside the `src` directory.'
            );
        }

        if (null === $outputDirectory) {
            $outputDirectory = Temporary::getTemporaryDirectory() . DS . 'Kitab.test.output' . DS . hash('sha256', realpath($directoryToScan)). DS;
        }

        Protocol::getInstance()['Kitab']['Output']->setReach("\r" . $outputDirectory . DS);

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
        } else {
            File\Directory::create($outputDirectory);
        }

        $target = new DocTest();

        $compiler = new Compiler();
        $compiler->compile($finder, $target);

        if (defined('KITAB_PHAR_NAME')) {
            $command = PHP_BINARY . ' ' . KITAB_PHAR_PATH . ' atoum';

            $temporaryAutoloaderPath = $outputDirectory . '.kitab.phar.autoloader.php';
            touch($temporaryAutoloaderPath);

            $temporaryAutoloader = new File\Write($temporaryAutoloaderPath, File::MODE_TRUNCATE_WRITE);
            $temporaryAutoloader->writeAll(
                '<?php' . "\n\n" .
                'Phar::loadPhar(\'' . KITAB_PHAR_PATH . '\', \'' . KITAB_PHAR_NAME . '\');' . "\n\n" .
                'require_once \'phar://'. KITAB_PHAR_NAME .'/vendor/autoload.php\';' . "\n" .
                (!empty($autoloader) ? 'require_once \'' . str_replace("'", "\\'", realpath($autoloader)) . '\';' : '')
            );

            $autoloader = $temporaryAutoloader->getStreamName();
        } else {
            $command =
                dirname(dirname(__DIR__)) . DS .
                'vendor' . DS .
                'atoum' . DS .
                'atoum' . DS .
                'bin' . DS .
                'atoum';

            if (false === file_exists($command)) {
                throw new \RuntimeException(
                    'Cannot locate `atoum` to execute the generated test suites.'
                );
            }

            $command .=
                ' --configurations ' .
                    dirname(__DIR__) . DS . 'DocTest' . DS . '.atoum.php';

            $temporaryAutoloaderPath = $outputDirectory . '.kitab.autoloader.php';
            touch($temporaryAutoloaderPath);

            $temporaryAutoloader = new File\Write($temporaryAutoloaderPath, File\File::MODE_TRUNCATE_WRITE);
            $temporaryAutoloader->writeAll(
                '<?php' . "\n\n" .
                'require_once \'' . str_replace("'", "\\'", realpath(dirname(__DIR__, 2) . DS . 'vendor' . DS . 'autoload.php')) . '\';' . "\n" .
                (!empty($autoloader) ? 'require_once \'' . str_replace("'", "\\'", realpath($autoloader)) . '\';' : '')
            );

            $autoloader = $temporaryAutoloader->getStreamName();
        }

        if (true === $verbose) {
            $command .= ' ++verbose';
        }

        $command .=
            ' --autoloader-file ' .
                $autoloader .
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
                'l'    => 'Path to the autoloader file.',
                'o'    => 'Directory that will receive the generated documentation.',
                'v'    => 'Be verbose (add some debug information).',
                'help' => 'This help.'
            ]);

        return;
    }
}
