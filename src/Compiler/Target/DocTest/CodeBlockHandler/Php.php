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

namespace Kitab\Compiler\Target\DocTest\CodeBlockHandler;

use Kitab\Compiler\Parser;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\PrettyPrinter;

/**
 * A code block handler for the `php` type.
 *
 * This handler defines the `php` type with the following options:
 *
 *   * `php,ignore` to avoid compiling the code block into a test case. The code
 *     block is just displayed in the documentation, but not tested,
 *   * `php,must_throw` to indicate that the code block must throw an exception,
 *     so catching an exception is expected,
 *   * `php,must_throw(E)` to indicate that the code block must throw an
 *     exception of kind `E`.
 */
class Php implements Definition
{
    /**
     * A traverser, for PHP-Parser, is a set of visitors visiting the Abstract
     * Syntax Tree produced by the parser. This traverser will be used to
     * pretty print the PHP code, and to make it embeddable inside a test
     * case.
     *
     * The traverser is allocated once, hence the static declaration.
     */
    protected static $_phpTraverser = null;

    /**
     * The handler name is `php`.
     *
     * # Examples
     *
     * ```php
     * $handler = new Kitab\Compiler\Target\DocTest\CodeBlockHandler\Php();
     *
     * assert('php' === $handler->getDefinitionName());
     * ```
     */
    public function getDefinitionName(): string
    {
        return 'php';
    }

    /**
     * Check whether a code block type contains `php` or is empty. The
     * consequence is that the `php` type will be assumed if a code block has
     * no type.
     *
     * # Examples
     *
     * All the following type syntaxes are handled:
     *
     * ```php
     * $handler = new Kitab\Compiler\Target\DocTest\CodeBlockHandler\Php();
     *
     * assert(true  === $handler->mightHandleCodeblock('php'));
     * assert(true  === $handler->mightHandleCodeblock('php,ignore'));
     * assert(true  === $handler->mightHandleCodeblock('php,must_throw'));
     * assert(false === $handler->mightHandleCodeblock('foobar'));
     * ```
     *
     * A code block with no type is assumed to be of type `php`:
     *
     * ```php
     * $handler = new Kitab\Compiler\Target\DocTest\CodeBlockHandler\Php();
     *
     * assert($handler->mightHandleCodeblock(''));
     * ```
     */
    public function mightHandleCodeblock(string $codeBlockType): bool
    {
        return empty($codeBlockType) || 0 !== preg_match('/\bphp\b/', $codeBlockType);
    }

    /**
     * Unfold the code block content, and compile it into a test case.
     *
     * # Examples
     *
     * A regular code block content:
     *
     * ```php
     * $handler = new Kitab\Compiler\Target\DocTest\CodeBlockHandler\Php();
     *
     * $codeBlockType    = 'php';
     * $codeBlockContent = 'assert(true);';
     * $output           =
     *     '$this' . "\n" .
     *     '    ->assert(function () {' . "\n" .
     *     '        \assert(\true);' . "\n" .
     *     '    });';
     *
     * assert($output === $handler->compileToTestCaseBody($codeBlockType, $codeBlockContent));
     * ```
     *
     * A code block that must not be tested:
     *
     * ```php
     * $handler = new Kitab\Compiler\Target\DocTest\CodeBlockHandler\Php();
     *
     * $codeBlockType    = 'php,ignore';
     * $codeBlockContent = 'assert(true);';
     * $output           = '$this->skip(\'Skipped because the code block type contains `ignore`: `php,ignore`.\');';
     *
     * assert($output === $handler->compileToTestCaseBody($codeBlockType, $codeBlockContent));
     * ```
     *
     * A code block that must throw an exception of kind `E`:
     *
     * ```php
     * $handler = new Kitab\Compiler\Target\DocTest\CodeBlockHandler\Php();
     *
     * $codeBlockType    = 'php,must_throw(E)';
     * $codeBlockContent = 'assert(true);';
     * $output           =
     *     '$this' . "\n" .
     *     '    ->exception(function () {' . "\n" .
     *     '        \assert(\true);' . "\n" .
     *     '    })' . "\n" .
     *     '        ->isInstanceOf(\E::class);';
     *
     * assert($output === $handler->compileToTestCaseBody($codeBlockType, $codeBlockContent));
     * ```
     */
    public function compileToTestCaseBody(string $codeBlockType, string $codeBlockContent): string
    {
        $codeBlockContent = $this->unfoldCode($codeBlockContent);

        if (0 !== preg_match('/\bignore\b/', $codeBlockType)) {
            return
                sprintf(
                    '$this->skip(\'Skipped because ' .
                    'the code block type contains `ignore`: `%s`.\');',
                    $codeBlockType
                );
        }

        if (0 !== preg_match('/\bmust_throw(?:\(([^\)]+)\)|\b)/', $codeBlockType, $matches)) {
            return
                sprintf(
                    '$this' . "\n" .
                    '    ->exception(function () {' . "\n" .
                    '        %s' . "\n" .
                    '    })' . "\n" .
                    '        ->isInstanceOf(\\%s::class);',
                    preg_replace(
                        '/^\h+$/m',
                        '',
                        str_replace("\n", "\n" . '    ', $codeBlockContent)
                    ),
                    isset($matches[1]) ? $matches[1] : 'Exception'
                );
        }

        return
            sprintf(
                '$this' . "\n" .
                '    ->assert(function () {' . "\n" .
                '        %s' . "\n" .
                '    });',
                preg_replace(
                    '/^\h+$/m',
                    '',
                    str_replace("\n", "\n" . '        ', $codeBlockContent)
                )
            );
    }

    /**
     * Prepare the code to be embeddable inside a test case.
     */
    protected function unfoldCode(string $phpCode): string
    {
        $ast = Parser::getPhpParser()->parse('<?php ' . $phpCode);
        $ast = self::getPhpTraverser()->traverse($ast);

        return Parser::getPhpPrettyPrinter()->prettyPrint($ast);
    }

    /**
     * Get the statically allocated traverser instance.
     */
    protected static function getPhpTraverser(): NodeTraverser
    {
        if (null === self::$_phpTraverser) {
            self::$_phpTraverser = new NodeTraverser();
            self::$_phpTraverser->addVisitor(new NodeVisitor\NameResolver());
            self::$_phpTraverser->addVisitor(new IntoPHPTestCaseBody());
        }

        return self::$_phpTraverser;
    }
}
