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

class Welcome extends Console\Dispatcher\Kit
{
    /**
     * Options description.
     *
     * @var array
     */
    protected $options = [
        ['list', Console\GetOption::NO_ARGUMENT, 'l'],
        ['help', Console\GetOption::NO_ARGUMENT, 'h'],
        ['help', Console\GetOption::NO_ARGUMENT, '?']
    ];

    private $_subCommands = [
        'compile' => 'to compile the documentation into static HTML files',
        'test'    => 'to test the documentation'
    ];



    /**
     * The entry method.
     *
     * @return  int
     */
    public function run()
    {
        $printList = false;

        while (false !== $c = $this->getOption($v)) {
            switch ($c) {
                case 'l':
                    $printList = $v;

                break;

                case 'h':
                case '?':
                    return $this->usage();

                case '__ambiguous':
                    $this->resolveOptionAmbiguity($v);

                    break;
            }
        }

        if (true === $printList) {
            echo implode("\t", array_keys($this->_subCommands));

            return;
        }

        echo
            'Welcome :-)!', "\n\n",
            'List of subcommands:', "\n\n",
            implode(
                ",\n",
                array_map(
                    function ($key, $value) {
                        return '  * `' . $key . '`, ' . $value;
                    },
                    array_keys($this->_subCommands),
                    array_values($this->_subCommands)
                )
            ), ".\n\n",
            'Example:', "\n\n",
            '    $ ', $_SERVER['argv'][0], ' compile src', "\n";

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
            'Usage   : welcome <options>', "\n",
            'Options :', "\n",
            $this->makeUsageOptionsList([
                'l'    => 'List available sub-commands.',
                'help' => 'This help.'
            ]);

        return;
    }
}
