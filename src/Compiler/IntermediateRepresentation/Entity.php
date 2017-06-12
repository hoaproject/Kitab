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

/**
 * An entity intermediate representation.
 *
 * This is an abstract class aiming at representing all kind of entity, like
 * class, interface, trait, function…
 */
abstract class Entity
{
    /**
     * The kind of the entity, defined as a simple string.
     */
    const TYPE = '(unknown)';

    /**
     * File name containing the entity.
     */
    public $file;

    /**
     * Unsigned integer representing where the entity declaration starts in the file.
     */
    public $lineStart     = 0;

    /**
     * Unsigned integer representing where the entity declaration ends in the file.
     */
    public $lineEnd       = 0;

    /**
     * The fully-qualified name of the entity, i.e. it includes namespaces.
     */
    public $name;

    /**
     * Associated documentation of the entity as an instance of
     * `Kitab\Compiler\IntermediateRepresentation\Documentation`.
     */
    public $documentation = '';

    /**
     * The name of the entity without the short name, i.e. only the namespaces.
     *
     * # Examples
     *
     * ```php
     * $class = new Kitab\Compiler\IntermediateRepresentation\Class_('Foo\\Bar\\Baz');
     *
     * assert($class->getNamespaceName() === 'Foo\\Bar');
     * ```
     */
    public function getNamespaceName(): string
    {
        if (false === $pos = strrpos($this->name, '\\')) {
            return '__global__';
        }

        return substr($this->name, 0, $pos);
    }

    /**
     * The name of the entity without the namespaces, only the short name.
     *
     * # Examples
     *
     * ```php
     * $class = new Kitab\Compiler\IntermediateRepresentation\Class_('Foo\\Bar\\Baz');
     *
     * assert($class->getShortName() === 'Baz');
     * ```
     */
    public function getShortName(): string
    {
        if (false === $pos = strrpos($this->name, '\\')) {
            return $this->name;
        }

        return substr($this->name, $pos + 1);
    }

    /**
     * An entity is in a namespace if its fully-qualified name contains at
     * least one namespace separator (aka `\`).
     *
     * # Examples
     *
     * ```php
     * $classA = new Kitab\Compiler\IntermediateRepresentation\Class_('Foo\\Bar\\Baz');
     *
     * assert(true === $class->inNamespace());

     * $classB = new Kitab\Compiler\IntermediateRepresentation\Class_('Qux');
     *
     * assert(false === $class->inNamespace());
     * ```
     */
    public function inNamespace(): bool
    {
        return false !== strpos($this->name, '\\');
    }

    /**
     * Return the type of the entity defined by the `TYPE` class constant.
     */
    public static function getType(): string
    {
        return static::TYPE;
    }
}
