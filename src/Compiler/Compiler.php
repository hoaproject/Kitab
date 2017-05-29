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

use Kitab\Configuration;
use Kitab\Exception;
use Kitab\Finder;
use PhpParser\ParserFactory;

/**
 * The compiler.
 *
 * It is responsible to orchestrate the whole compilation process.
 */
class Compiler
{
    protected $_configuration = null;
    protected static $_parser = null;

    /**
     * When constructing the compiler, a new instance of the parser
     * `Kitab\Compiler\Parser` is created and stored statically. This is a way
     * to cache the parser. This is not necessary to allocate a new parser
     * each time.
     */
    public function __construct(Configuration $configuration = null)
    {
        if (null === $configuration) {
            $configuration = new Configuration();
        }

        $this->_configuration = $configuration;

        if (null === self::$_parser) {
            self::$_parser = new Parser();
        }

        return;
    }

    /**
     * Compile all files provided by the finder into the specified target.
     * The compilation process is very classical. It works as follows:
     *
     *   1. For each file provided by the finder,
     *   2. Parser the file, and get an intermediate representation,
     *   3. Compile the intermediate representation based on the given target,
     *   4. Update the linker with new symbols,
     *   5. Go to 1 if the finder is not empty,
     *   6. Ask the target to assemble all the generated objects.
     *
     * With this approach, only one intermediate representation of a file is
     * allocated in the memory at a time. Not all the representations for all
     * the files are in memory. It avoids having huge allocations, and big
     * peaks, with all the complications.
     *
     * # Examples
     *
     * Bla bla foo bar:
     *
     * ```php
     * assert(3 === 2 + 1);
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
     */
    protected function getParser(): Parser
    {
        return self::$_parser;
    }

    /**
     * Extract new symbols from an intermediate representation and update the linker.
     *
     * The linker is a tree structure. Bla bla.
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
