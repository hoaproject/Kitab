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

/**
 * A trait intermediate representation.
 *
 * A trait is one of the major entity in PHP. It exposes attributes, and
 * methods. A trait can inherit from one other trait.
 *
 * # Examples
 *
 * In this example, a new trait `T` is built, with 2 attributes: `foo`
 * and `bar`, and one method: `f`.
 *
 * ```php
 * $trait               = new Kitab\Compiler\IntermediateRepresentation\Trait_('T');
 * $trait->attributes[] = new Kitab\Compiler\IntermediateRepresentation\Attribute('foo');
 * $trait->attributes[] = new Kitab\Compiler\IntermediateRepresentation\Attribute('bar');
 * $trait->methods[]    = new Kitab\Compiler\IntermediateRepresentation\Method('f');
 * ```
 */
class Trait_ extends Entity
{
    /**
     * Type of the entity. See parent.
     */
    const TYPE = 'trait';

    /**
     * Fully-qualified name of the trait it extends if any.
     */
    public $parent     = null;

    /**
     * Collection of `Kitab\Compiler\IntermediateRepresentation\Attribute` instances.
     */
    public $attributes = [];

    /**
     * Collection of `Kitab\Compiler\IntermediateRepresentation\Method` instances.
     */
    public $methods    = [];

    /**
     * Allocate a trait with a fully-qualified name. This is the only
     * mandatory information.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
