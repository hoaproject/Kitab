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

namespace Kitab\Compiler;

use Kitab\Exception;
use Kitab\Finder;
use StdClass;

/**
 * A compiler that orchestrates the whole compilation process.
 *
 * This compiler, [as explained previously](kitab/compiler/index.html), is a
 * stream compiler. It receives a finder that is an iterator where each item
 * is a PHP file to analyse. Each file is parsed by the parser, transformed
 * into a Intermediate Representation, that is compiled by the target into
 * partial objects. The target ends the whole process by assembling all the
 * objects.
 */
class Compiler
{
    /**
     * The parsed used to parse PHP files.
     *
     * The parser is allocated once, hence the static declaration.
     */
    protected static $_parser = null;

    /**
     * When constructing the compiler, a new instance of the
     * `Kitab\Compiler\Parser` parser is created and stored statically.
     */
    public function __construct()
    {
        if (null === self::$_parser) {
            self::$_parser = new Parser();
        }

        return;
    }

    /**
     * Compile all files provided by the finder into the specified target.
     * See [the workflow description](kitab/compiler/index.html) for more details.
     *
     * # Examples
     *
     * ```php,ignore
     * $finder = new Kitab\Finder();
     * $finder->in($path);
     *
     * $target = new Kitab\Compiler\Target\Html\Html();
     *
     * $compiler = new Kitab\Compiler\Compiler();
     * $compiler->compile($finder, $target);
     * ```
     */
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

    /**
     * Return the parser stored in the cache.
     *
     * # Examples
     *
     * ```php,ignore
     * $compiler = new Kitab\Compiler\Compiler();
     *
     * assert($compiler->getParser() instanceof Kitab\Compiler\Parser);
     * ```
     */
    public function getParser(): Parser
    {
        return self::$_parser;
    }

    /**
     * Extract new symbols from an intermediate representation and update the linker.
     *
     * The linker is a tree structure.
     *
     * # Exceptions
     *
     * If the link handles an intermediate representation that is unexpected,
     * a `Kitab\Exception\LinkerUnknownIntermediateRepresentation` exception
     * will be thrown. This is not likely to happen, except during a
     * development phase.
     */
    protected function link(IntermediateRepresentation\File $file, array &$symbols)
    {
        foreach ($file as $item) {
            if ($item instanceof IntermediateRepresentation\Entity) {
                $symbolParts      = explode('\\', $item->getNamespaceName());
                $lastSymbolPart   = '@' . $item->getType() . ':' . $item->getShortName();
                $currentDimension = &$symbols;

                foreach ($symbolParts as $symbolPart) {
                    if (!isset($currentDimension[$symbolPart])) {
                        $currentDimension[$symbolPart] = [];
                    }

                    $currentDimension = &$currentDimension[$symbolPart];
                }

                $symbol              = new StdClass();
                $symbol->name        = $item->name;
                $symbol->description = $item->documentation;

                $currentDimension[$lastSymbolPart] = $symbol;
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
