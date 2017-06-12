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
 * A class intermediate representation.
 *
 * A class is one of the major entity in PHP. It exposes constants,
 * attributes, and methods, in addition to some properties (like `final`,
 * `abstract` etc.). A class can inherit from one other class, and can
 * implement one or more interfaces.

 * # Examples
 *
 * In this example, a new final class `C` is built, with 2 attributes: `foo`
 * and `bar`, and one method: `f`.
 *
 * ```php
 * $class               = new Kitab\Compiler\IntermediateRepresentation\Class_('C');
 * $class->attributes[] = new Kitab\Compiler\IntermediateRepresentation\Attribute('foo');
 * $class->attributes[] = new Kitab\Compiler\IntermediateRepresentation\Attribute('bar');
 * $class->methods[]    = new Kitab\Compiler\IntermediateRepresentation\Method('f');
 * ```
 */
class Class_ extends Entity
{
    /**
     * Type of the entity. See parent.
     */
    const TYPE = 'class';

    /**
     * Represent whether the class is final or not.
     *
     * A final class cannot be extended. It is a child of the inheritance hierarchy.
     */
    public $final      = false;

    /**
     * Represent whether the class is abstract or not.
     *
     * An abstract class cannot be instanciated. In addition, some methods can
     * be marked as abstract too. Such methods have no implementations
     * (bodies), they only provide a signature (inputs and output).
     */
    public $abstract   = false;

    /**
     * Fully-qualified name of the class it extends if any.
     */
    public $parent     = null;

    /**
     * Fully-qualified names of the interfaces it implements if any.
     */
    public $interfaces = [];

    /**
     * Collection of `Kitab\Compiler\IntermediateRepresentation\Constant` instances.
     */
    public $constants  = [];

    /**
     * Collection of `Kitab\Compiler\IntermediateRepresentation\Attribute` instances.
     */
    public $attributes = [];

    /**
     * Collection of `Kitab\Compiler\IntermediateRepresentation\Method` instances.
     */
    public $methods    = [];

    /**
     * Allocate a class with a fully-qualified name. This is the only mandatory information.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
