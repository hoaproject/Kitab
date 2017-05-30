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
use PhpParser\PrettyPrinter;

class Into extends NodeVisitorAbstract
{
    protected $_file        = null;
    private $_prettyPrinter = null;

    public function __construct(string $filename)
    {
        $this->_file          = new File($filename);
        $this->_prettyPrinter = new PrettyPrinter\Standard(['shortArraySyntax' => true]);
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $classNode            = $node;
            $class                = new Class_($classNode->namespacedName->toString());
            $class->lineStart     = $classNode->getAttribute('startLine');
            $class->lineEnd       = $classNode->getAttribute('endLine');
            $class->documentation = Parser::extractFromComment($classNode->getDocComment());
            $class->constants     = $this->intoConstants($classNode);
            $class->attributes    = $this->intoAttributes($classNode);
            $class->methods       = $this->intoMethods($classNode);

            if ($classNode->flags & Node\Stmt\Class_::MODIFIER_ABSTRACT) {
                $class->abstract = true;
            }

            if ($classNode->flags & Node\Stmt\Class_::MODIFIER_FINAL) {
                $class->final = true;
            }

            if (null !== $classNode->extends) {
                $class->parent = $classNode->extends->toString();
            }

            foreach ($classNode->implements as $interfaceNameNode) {
                $class->interfaces[] = $interfaceNameNode->toString();
            }

            $this->_file[] = $class;
        } elseif ($node instanceof Node\Stmt\Interface_) {
            $interfaceNode            = $node;
            $interface                = new Interface_($interfaceNode->namespacedName->toString());
            $interface->lineStart     = $interfaceNode->getAttribute('startLine');
            $interface->lineEnd       = $interfaceNode->getAttribute('endLine');
            $interface->documentation = Parser::extractFromComment($interfaceNode->getDocComment());
            $interface->constants     = $this->intoConstants($interfaceNode);
            $interface->methods       = $this->intoMethods($interfaceNode);

            if (!empty($interfaceNode->extends)) {
                $interface->parents = array_map(
                    function ($nodeName) {
                        return $nodeName->toString();
                    },
                    $interfaceNode->extends
                );
            }

            $this->_file[] = $interface;
        } elseif ($node instanceof Node\Stmt\Trait_) {
            $traitNode        = $node;
            $trait            = new Trait_($traitNode->namespacedName->toString());
            $trait->lineStart = $traitNode->getAttribute('startLine');
            $trait->lineEnd   = $traitNode->getAttribute('endLine');
            $trait->methods   = $this->intoMethods($traitNode);

            $this->_file[] = $trait;
        } elseif ($node instanceof Node\Stmt\Function_) {
            $functionNode            = $node;
            $function                = new Function_($functionNode->namespacedName->toString());
            $function->lineStart     = $functionNode->getAttribute('startLine');
            $function->lineEnd       = $functionNode->getAttribute('endLine');
            $function->documentation = Parser::extractFromComment($functionNode->getDocComment());
            $function->inputs        = $this->intoInputs($functionNode);
            $function->output        = $this->intoOutput($functionNode);

            $this->_file[] = $function;
        }

        return;
    }

    protected function intoConstants(Node\Stmt\ClassLike $node): array
    {
        $constants = [];

        foreach ($node->stmts as $statement) {
            if (!($statement instanceof Node\Stmt\ClassConst)) {
                continue;
            }

            $defaultDocumentation = Parser::extractFromComment($statement->getDocComment());

            if (true === $statement->isPublic()) {
                $visibility = Constant::VISIBILITY_PUBLIC;
            } else if (true === $statement->isProtected()) {
                $visibility = Constant::VISIBILITY_PROTECTED;
            } else {
                $visibility = Constant::VISIBILITY_PRIVATE;
            }

            foreach ($statement->consts as $constantNode) {
                $constant             = new Constant($constantNode->name);
                $constant->visibility = $visibility;
                $constant->value      = $this->_prettyPrinter->prettyPrint([$constantNode->value]);

                $documentation = Parser::extractFromComment($constantNode->getDocComment());

                if (empty($documentation)) {
                    $constant->documentation = $defaultDocumentation;
                } else {
                    $constant->documentation = $documentation;
                }

                $constants[] = $constant;
            }
        }

        return $constants;
    }

    protected function intoAttributes(Node\Stmt\ClassLike $node): array
    {
        $attributes = [];

        foreach ($node->stmts as $statement) {
            if (!($statement instanceof Node\Stmt\Property)) {
                continue;
            }

            $defaultDocumentation = Parser::extractFromComment($statement->getDocComment());

            if (true === $statement->isPublic()) {
                $visibility = Attribute::VISIBILITY_PUBLIC;
            } else if (true === $statement->isProtected()) {
                $visibility = Attribute::VISIBILITY_PROTECTED;
            } else {
                $visibility = Attribute::VISIBILITY_PRIVATE;
            }

            $static = $statement->isStatic();

            foreach ($statement->props as $attributeNode) {
                $attribute             = new Attribute($attributeNode->name);
                $attribute->visibility = $visibility;

                if (null !== $attributeNode->default) {
                    $attribute->default = $this->_prettyPrinter->prettyPrint([$attributeNode->default]);
                }

                $documentation = Parser::extractFromComment($attributeNode->getDocComment());

                if (empty($documentation)) {
                    $attribute->documentation = $defaultDocumentation;
                } else {
                    $attribute->documentation = $documentation;
                }

                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    protected function intoMethods(Node\Stmt\ClassLike $node): array
    {
        $methods = [];

        foreach ($node->getMethods() as $methodNode) {
            $method            = new Method($methodNode->name);
            $method->lineStart = $methodNode->getAttribute('startLine');
            $method->lineEnd   = $methodNode->getAttribute('endLine');

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

            $method->inputs = $this->intoInputs($methodNode);
            $method->output = $this->intoOutput($methodNode);

            $methods[] = $method;
        }

        return $methods;
    }

    protected function intoInputs($node): array
    {
        $inputs         = [];
        $parametersNode = $node->params;

        foreach ($parametersNode as $parameterNode) {
            $parameter                  = new Parameter($parameterNode->name);
            $parameter->type            = $this->intoType($parameterNode->type);
            $parameter->type->reference = $parameterNode->byRef;
            $parameter->type->variadic  = $parameterNode->variadic;

            $inputs[] = $parameter;
        }

        return $inputs;
    }

    protected function intoOutput($node): Type
    {
        $output            = $this->intoType($node->returnType);
        $output->reference = $node->byRef;

        return $output;
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
