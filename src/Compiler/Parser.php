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

use Hoa\File;
use Kitab\Exception;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\ParserFactory;
use PhpParser\Parser\Multiple as ParserMultiple;
use PhpParser\PrettyPrinter;

/**
 * A parser producing an Intermediate Representation.
 *
 * This parser takes one file, parses it, generates an Abstract Syntax Tree,
 * and transforms it into an Intermediate Representation.
 *
 * The PHP 7 form is prefered over PHP 5 and lower forms, it means it will try
 * to parse with PHP 7 strategy first. They are small but subtle [differences
 * with previous PHP
 * versions](http://php.net/manual/en/migration70.incompatible.php#migration70.incompatible.variable-handling).
 *
 * All the work done by the parser is delegated to
 * [PHP-Parser](https://github.com/nikic/PHP-Parser).
 */
class Parser
{
    /**
     * The PHP parser, aka [PHP-Parser](https://github.com/nikic/PHP-Parser),
     * that is used to parse PHP files.
     *
     * The PHP parser is allocated once, hence the static declaration.
     */
    protected static $_phpParser        = null;

    /**
     * A traverser, for PHP-Parser, is a set of visitors visiting the Abstract
     * Syntax Tree produced by the parser. This traverser will be used to
     * apply visitors on the AST. It always contain a name resolver visitor,
     * to get fully qualified names everywhere in the code.
     *
     * The traverser is allocated once, hence the static declaration.
     */
    protected static $_phpTraverser     = null;

    /**
     * Pretty print visitor to transform a PHP AST into its PHP representation.
     *
     * The pretty printer is allocated once, hence the static declaration.
     */
    protected static $_phpPrettyPrinter = null;

    /**
     * The `parse` methods parses a file aiming at containing PHP code, and
     * produces the Intermediate Representation of it if valid. [Get more
     * information about the general workflow](kitab/compiler/index.html).
     *
     * # Examples
     *
     * ```php,ignore
     * $file   = new Hoa\File\SplFileInfo('path/to/a/file.php');
     * $parser = new Kitab\Compiler\Parser();
     *
     * $intermediateRepresentation = $parser->parse($file);
     * ```
     *
     * # Exceptions
     *
     * The `Kitab\Exception\PhpParserError` can be thrown if the given file
     * does not contain valid PHP code. The exception contains the `Error`
     * exception from PHP-Parser, which holds more information, as a previous
     * exception.
     */
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

        return $this->intoIntermediateRepresentation($statements, $fileName);
    }

    /**
     * Transform the Abstract Syntax Tree into its Intermediate Representation.
     *
     * In PHP-Parser, the AST is a hashmap of n-dimensions of statements. The
     * produced IR is a tree of structures. The file name of the original file
     * is kept to provide more context.
     *
     * The transformation is applied by a visitor. It is added on the
     * traverser (see `self::$_phpTraverser`), run, and remove. The visitor
     * contains the resulting IR.
     */
    protected function intoIntermediateRepresentation(
        array $statements,
        string $fileName
    ): IntermediateRepresentation\File {
        $intoIR = new IntermediateRepresentation\Into($fileName);

        $traverser = self::getTraverser();
        $traverser->addVisitor($intoIR);
        $traverser->traverse($statements);
        $traverser->removeVisitor($intoIR);

        return $intoIR->collect();
    }

    /**
     * Get the statically allocated PHP-Parser instance.
     */
    public static function getPhpParser(): ParserMultiple
    {
        if (null === self::$_phpParser) {
            self::$_phpParser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        }

        return self::$_phpParser;
    }

    /**
     * Get the statically allocated traverser instance.
     */
    protected static function getTraverser(): NodeTraverser
    {
        if (null === self::$_phpTraverser) {
            self::$_phpTraverser = new NodeTraverser();
            self::$_phpTraverser->addVisitor(new NodeVisitor\NameResolver());
        }

        return self::$_phpTraverser;
    }

    /**
     * Get the statically allocated pretty printer
     */
    public static function getPhpPrettyPrinter(): PrettyPrinter\Standard
    {
        if (null === self::$_phpPrettyPrinter) {
            self::$_phpPrettyPrinter = new PrettyPrinter\Standard(['shortArraySyntax' => true]);
        }

        return self::$_phpPrettyPrinter;
    }

    /**
     * Extract content from a comment of kind block (`/**`).
     *
     * This is a small utility used to extract documentation from the code.
     *
     * # Examples
     *
     * ```php
     * $content = 'foobar';
     * $input   = '/**' . $content . '*' . '/';
     *
     * assert(Kitab\Compiler\Parser::extractFromComment($input) === $content);
     * ```
     */
    public static function extractFromComment($comment)
    {
        return preg_replace(
            ',(^(/\*\*|\h*\*/?\h*)|\*/$),m',
            '',
            $comment
        );
    }
}
