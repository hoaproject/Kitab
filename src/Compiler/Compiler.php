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

use Kitab\Finder;
use Kitab\Exception;
use PhpParser\ParserFactory;

class Compiler
{
    protected static $_parser = null;

    public function __construct()
    {
        if (null === self::$_parser) {
            self::$_parser = new Parser();
        }

        return;
    }

    public function compile(Finder $finder, Target\Target $target)
    {
        $parser  = self::getParser();
        $symbols = [];

        foreach ($finder as $file) {
            $intermediateRepresentation = $parser->parse($file);
            $target->compile($intermediateRepresentation);

            $this->link($intermediateRepresentation, $symbols);
        }

        $target->assemble($symbols);

        return;
    }

    protected function getParser(): Parser
    {
        return self::$_parser;
    }

    protected function link(IntermediateRepresentation\File $file, array &$symbols)
    {
        foreach ($file as $item) {
            if ($item instanceof IntermediateRepresentation\Class_) {
                $symbolParts      = explode('\\', $item->name);
                $lastSymbolPart   = '@class:' . array_pop($symbolParts);
                $currentDimension = &$symbols;

                foreach($symbolParts as $symbolPart) {
                    if (!isset($currentDimension[$symbolPart])) {
                        $currentDimension[$symbolPart] = [];
                    }

                    $currentDimension = &$currentDimension[$symbolPart];
                }

                $currentDimension[$lastSymbolPart] = $item->name;
            } else {
                throw new Exception\LinkerUnknownIntermediateRepresentation(
                    'Linker does not handle the following intermediate ' .
                    'representation: `%s`.',
                    0,
                    get_class($item)
                );
            }
        }
    }
}
