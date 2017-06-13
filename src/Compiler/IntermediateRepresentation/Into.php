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

namespace Kitab\Compiler\IntermediateRepresentation;

use Kitab\Compiler\Parser;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter;

/**
 * A visitor to transform an Abstract Syntax Tree (AST) into an Intermediate
 * Representation (IR).
 *
 * This visitor implements the API from `PhpParser\NodeVisitorAbstract`. It is
 * applied when leaving a node of the AST to allow other visitors to transform
 * the currently visited node and its children. For instance, the name
 * resolver visitor modifies the AST when entering a node. This visitor needs
 * the information from the name resolver visitor, so it must executes
 * after. The best way to get all these information for a node and its
 * children is to run each node when the visitor leaves a node.
 *
 * # Examples
 *
 * See [the examples of the current
 * namespace](kitab/compiler/intermediaterepresentation/index.html).
 */
class Into extends NodeVisitorAbstract
{
    /**
     * Name of the file where the AST comes from.
     */
    protected $_file        = null;

    /**
     * Pretty print visitor to transform the AST into its PHP representation.
     *
     * It is used for example to get the PHP representation of a default value
     * for an attribute.
     */
    private $_prettyPrinter = null;

    /**
     * Allocate a new visitor. The only mandatory information is the name of
     * the file where the AST comes from.
     */
    public function __construct(string $filename)
    {
        $this->_file          = new File($filename);
        $this->_prettyPrinter = new PrettyPrinter\Standard(['shortArraySyntax' => true]);
    }

    /**
     * Transform a node of the AST into an IR.
     *
     * This method returns nothing because it is called several times by the
     * traverser API. To get the final result, see the `collect` method.
     */
    public function leaveNode(Node $node): void
    {
        if ($node instanceof Node\Stmt\Class_) {
            $classNode            = $node;
            $class                = new Class_($classNode->namespacedName->toString());
            $class->lineStart     = $classNode->getAttribute('startLine');
            $class->lineEnd       = $classNode->getAttribute('endLine');
            $class->documentation = new Documentation(Parser::extractFromComment($classNode->getDocComment()));
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
            $interface->documentation = new Documentation(Parser::extractFromComment($interfaceNode->getDocComment()));
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
            $function->documentation = new Documentation(Parser::extractFromComment($functionNode->getDocComment()));
            $function->inputs        = $this->intoInputs($functionNode);
            $function->output        = $this->intoOutput($functionNode);

            $this->_file[] = $function;
        }
    }

    /**
     * Extract constant nodes and transform them into a collection of
     * `Kitab\Compiler\IntermediateRepresentation\Constant` objects.
     *
     * It supports both declaration forms:
     *
     *  * `public const FOO = 42; public const BAR = 153;`, and
     *  * `public const FOO = 42, BAR = 153;`.
     *
     * The resulting IR will be the same and will correspond to the former form.
     */
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
            } elseif (true === $statement->isProtected()) {
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
                    $constant->documentation = new Documentation($defaultDocumentation);
                } else {
                    $constant->documentation = new Documentation($documentation);
                }

                $constants[] = $constant;
            }
        }

        return $constants;
    }

    /**
     * Extract attribute nodes and transform them into a collection of
     * `Kitab\Compiler\IntermediateRepresentation\Attribute` objects.
     *
     * It supports both declaration forms:
     *
     *  * `public $foo = 42; public $bar = 153;`, and
     *  * `public $foo = 42, $bar = 153;`.
     *
     * The resulting IR will be the same and will correspond to the former form.
     */
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
            } elseif (true === $statement->isProtected()) {
                $visibility = Attribute::VISIBILITY_PROTECTED;
            } else {
                $visibility = Attribute::VISIBILITY_PRIVATE;
            }

            $static = $statement->isStatic();

            foreach ($statement->props as $attributeNode) {
                $attribute             = new Attribute($attributeNode->name);
                $attribute->visibility = $visibility;
                $attribute->static     = $static;

                if (null !== $attributeNode->default) {
                    $attribute->default = $this->_prettyPrinter->prettyPrint([$attributeNode->default]);
                }

                $documentation = Parser::extractFromComment($attributeNode->getDocComment());

                if (empty($documentation)) {
                    $attribute->documentation = new Documentation($defaultDocumentation);
                } else {
                    $attribute->documentation = new Documentation($documentation);
                }

                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Extract method nodes and transform them into a collection of
     * `Kitab\Compiler\IntermediateRepresentation\Method` objects.
     */
    protected function intoMethods(Node\Stmt\ClassLike $node): array
    {
        $methods = [];

        foreach ($node->getMethods() as $methodNode) {
            $method            = new Method($methodNode->name);
            $method->lineStart = $methodNode->getAttribute('startLine');
            $method->lineEnd   = $methodNode->getAttribute('endLine');

            // Documentation.
            $method->documentation = new Documentation(Parser::extractFromComment($methodNode->getDocComment()));

            // Visibility, scope, and abstract.
            if (true === $methodNode->isPublic()) {
                $method->visibility = $method::VISIBILITY_PUBLIC;
            } elseif (true === $methodNode->isProtected()) {
                $method->visibility = $method::VISIBILITY_PROTECTED;
            } else {
                $method->visibility = $method::VISIBILITY_PRIVATE;
            }

            $method->static   = $methodNode->isStatic();
            $method->abstract = $methodNode->isAbstract();
            $method->final    = $methodNode->isFinal();

            $method->inputs = $this->intoInputs($methodNode);
            $method->output = $this->intoOutput($methodNode);

            $methods[] = $method;
        }

        return $methods;
    }

    /**
     * Extract nodes representing parameters of a function and transform them
     * into a collection of
     * `Kitab\Compiler\IntermediateRepresentation\Parameter` objects.
     */
    protected function intoInputs($node): array
    {
        $inputs         = [];
        $parametersNode = $node->params;

        foreach ($parametersNode as $parameterNode) {
            $parameter                  = new Parameter($parameterNode->name);
            $parameter->type            = $this->intoType($parameterNode->type);
            $parameter->type->reference = $parameterNode->byRef;
            $parameter->variadic        = $parameterNode->variadic;

            $inputs[] = $parameter;
        }

        return $inputs;
    }

    /**
     * Extract node representing the output of a function and transform it
     * into a `Kitab\Compiler\IntermediateRepresentation\Type` object.
     */
    protected function intoOutput($node): Type
    {
        $output            = $this->intoType($node->returnType);
        $output->reference = $node->byRef;

        return $output;
    }

    /**
     * Extract node representing a type and transform it into a
     * `Kitab\Compiler\IntermediateRepresentation\Type` object.
     */
    protected function intoType($node): Type
    {
        $type = new Type();

        if ($node instanceof Node\Name) {
            $type->name = $node->toString();
        } elseif ($node instanceof Node\NullableType) {
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

    /**
     * Because the visitor runs for every node in the AST, the only way to
     * collect the resulting IR is to call this method.
     *
     * This method can be called at any time but it is best to call it when
     * the traverser returns. It means the transformation will be complete.
     */
    public function collect(): File
    {
        return $this->_file;
    }
}
