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

class Php implements Definition
{
    protected static $_phpTraverser = null;

    public function getDefinitionName(): string
    {
        return 'php';
    }

    public function mightHandleCodeblock(string $codeBlockType): bool
    {
        return empty($codeBlockType) || 0 !== preg_match('/\bphp\b/', $codeBlockType);
    }

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
                    '        ->isInstanceOf(\'%s\');',
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

    protected function unfoldCode(string $phpCode): string
    {
        $ast = Parser::getPhpParser()->parse('<?php ' . $phpCode);
        $ast = self::getPhpTraverser()->traverse($ast);

        return Parser::getPhpPrettyPrinter()->prettyPrint($ast);
    }

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
