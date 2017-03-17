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

namespace Kitab\Compiler\IntermediateRepresentation;

use Kitab\Compiler\Parser;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class Into extends NodeVisitorAbstract
{
    protected $_file = null;

    public function __construct()
    {
        $this->_file = new File();
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $classNode      = $node;
            $class          = new Class_($classNode->namespacedName->toString());
            $class->methods = $this->intoMethods($node);

            $this->_file[] = $class;
        } elseif ($node instanceof Node\Stmt\Interface_) {
            $interfaceNode      = $node;
            $interface          = new Interface_($interfaceNode->namespacedName->toString());
            $interface->methods = $this->intoMethods($node);

            $this->_file[] = $interface;
        } elseif ($node instanceof Node\Stmt\Trait_) {
            $traitNode      = $node;
            $trait          = new Trait_($traitNode->namespacedName->toString());
            $trait->methods = $this->intoMethods($node);

            $this->_file[] = $trait;
        } elseif ($node instanceof Node\Stmt\Function_) {
            $functionNode      = $node;
            $function          = new Function_($functionNode->namespacedName->toString());

            $this->_file[] = $function;
        }

        return;
    }

    protected function intoMethods(Node\Stmt\ClassLike $node): array
    {
        $methods = [];

        foreach ($node->getMethods() as $methodNode) {
            $method = new Method($methodNode->name);

            // Documentation.
            $method->documentation = Parser::extractFromComment($methodNode->getDocComment());

            // Visibility, scope, and abstract.
            if ($methodNode->flags & Node\Stmt\Class_::MODIFIER_PUBLIC) {
                $method->visibility = $method::VISIBILITY_PUBLIC;
            } else if ($methodNode->flags & Node\Stmt\Class_::MODIFIER_PROTECTED) {
                $method->visibility = $method::VISIBILITY_PROTECTED;
            } else if ($methodNode->flags & Node\Stmt\Class_::MODIFIER_PRIVATE) {
                $method->visibility = $method::VISIBILITY_PRIVATE;
            }

            if ($methodNode->flags & Node\Stmt\Class_::MODIFIER_STATIC) {
                $method->static = true;
            }

            if ($methodNode->flags & Node\Stmt\Class_::MODIFIER_ABSTRACT) {
                $method->abstract = true;
            }

            if ($methodNode->flags & Node\Stmt\Class_::MODIFIER_FINAL) {
                $method->final = true;
            }

            // Inputs.
            $paramsNode = $methodNode->params;

            foreach ($paramsNode as $paramNode) {
                $parameter                  = new Parameter($paramNode->name);
                $parameter->type            = $this->intoType($paramNode->type);
                $parameter->type->reference = $paramNode->byRef;
                $parameter->type->variadic  = $paramNode->variadic;

                $method->inputs[] = $parameter;
            }

            // Output.
            $output            = $this->intoType($methodNode->returnType);
            $output->reference = $methodNode->byRef;
            $method->output    = $output;

            $methods[] = $method;
        }

        return $methods;
    }

    protected function intoType($node): Type
    {
        $type = new Type();

        if ($node instanceof Node\Name) {
            $type->name = $node->toString();
        } else if ($node instanceof Node\NullableType) {
            $type->nullable = true;

            $nullableNode = $node->type;

            if ($nullableNode instanceof Node\Name) {
                $type->name = $nullableNode->toString();
            } else {
                $type->name = $nullableNode;
            }
        } else {
            $type->name = $node;
        }

        return $type;
    }

    public function collect(): File
    {
        return $this->_file;
    }
}
