<?php

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

namespace Kitab\Compiler;

use Hoa\File;
use Kitab\Exception;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\ParserFactory;
use PhpParser\Parser\Multiple as ParserMultiple;

class Parser
{
    protected static $_phpParser    = null;
    protected static $_phpTraverser = null;

    public function __construct()
    {
        if (null === self::$_phpParser) {
            self::$_phpParser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        }

        if (null === self::$_phpTraverser) {
            self::$_phpTraverser = new NodeTraverser();
            self::$_phpTraverser->addVisitor(new NodeVisitor\NameResolver());
        }
    }

    public function parse(File\SplFileInfo $file): IntermediateRepresentation\File
    {
        $phpParser = self::getPhpParser();
        $fileName  = $file->getPathName();

        try {
            $statements = $phpParser->parse(file_get_contents($fileName));
        } catch (Error $e) {
            throw new Exception\PhpParserError(
                'A syntax error has been found in the file `%s`:' . "\n" .
                '> %s',
                0,
                [$fileName, $e->getMessage()],
                $e
            );
        }

        return $this->intoIntermediateRepresentation($statements);
    }

    protected function intoIntermediateRepresentation(array $statements): IntermediateRepresentation\File
    {
        $intoIR = new IntermediateRepresentation\Into();

        $traverser = self::getTraverser();
        $traverser->addVisitor($intoIR);
        $traverser->traverse($statements);
        $traverser->removeVisitor($intoIR);

        return $intoIR->collect();
    }

    protected static function getPhpParser(): ParserMultiple
    {
        return self::$_phpParser;
    }

    protected static function getTraverser(): NodeTraverser
    {
        return self::$_phpTraverser;
    }
}
