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

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * A visitor to transform an Abstract Syntax Tree (AST) into a test case body
 * candidate.
 *
 * So far, it applies the following transformations:
 *
 *   * Remove the `use` statements, assuming all names have been resolved.
 *
 * # Examples
 *
 * ```php
 * $code = '<?php use A\B\C; new C();';
 * $ast  = Kitab\Compiler\Parser::getPhpParser()->parse($code);
 *
 * $traverser = new PhpParser\NodeTraverser();
 * $traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver());
 * $traverser->addVisitor(new Kitab\Compiler\Target\DocTest\CodeBlockHandler\IntoPHPTestCaseBody());
 *
 * $ast = $traverser->traverse($ast);
 *
 * $testCaseCandidate = Kitab\Compiler\Parser::getPhpPrettyPrinter()->prettyPrint($ast);
 *
 * assert('new \A\B\C();' === $testCaseCandidate);
 * ```
 */
class IntoPHPTestCaseBody extends NodeVisitorAbstract
{
    public function leaveNode(Node $node) {
        if ($node instanceof Node\Stmt\Use_ ||
            $node instanceof Node\Stmt\GroupUse) {
            return NodeTraverser::REMOVE_NODE;
        }

        return null;
    }
}
