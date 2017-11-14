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

namespace Kitab\Compiler\Target\DocTest;

use Kitab;

/**
 * Configuration structure for the DocTest target.
 *
 * This structure contains all the configuration items used by the DocTest
 * target of Kitab. It extends the default Kitab configuration structure.
 *
 *
 * # Examples
 *
 * ```php
 * $configuration                      = new Kitab\Compiler\Target\DocTest\Configuration();
 * $configuration->concurrentProcesses = 2;
 *
 * assert(2 === $configuration->concurrentProcesses);
 * ```
 */
class Configuration extends Kitab\Configuration
{
    /**
     * Path to the autoloader file.
     *
     * Tests are likely to require an autoloader to load entities, like
     * classes. If Composer is used as a dependency manager, then the
     * autoloader file should be `vendor/autoload.php`.
     */
    public $autoloaderFile        = null;

    /**
     * Maximum concurrent processes that can be used to run the tests.
     */
    public $concurrentProcesses   = 4;

    /**
     * Bypass the compilation cache.
     *
     * If `true`, the compiler will compile test suites like it is for the
     * first time.
     */
    public $bypassCache           = false;

    /**
     * Code block handler names.
     *
     * List of code block handler class names. All the listed classes must
     * implement the
     * `Kitab\Compiler\Target\DocTest\CodeBlockHandler\Definition`
     * interface. A handler is responsible to compile a code block content
     * into a test case body. If you write your own code block handlers, it is
     * the correct place to declare them.
     *
     * The code block handler for `php` code block type is already declared.
     */
    public $codeBlockHandlerNames = [
        CodeBlockHandler\Php::class
    ];
}
