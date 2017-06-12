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
 * A named function intermediate representation.
 *
 * A named function is one of the major entity in PHP. It has zero or many
 * inputs, and zero or one output. Each input is represented by a
 * `Kitab\Compiler\IntermediateRepresentation\Parameter` instance, while the
 * output is represented by a `Kitab\Compiler\IntermediateRepresentation\Type`
 * instance.
 *
 * # Examples
 *
 * In this example, a new function `f` is created with 1 input: `int $x`, and
 * 1 output: `int`.
 *
 * ```php
 * $typeInt = new Kitab\Compiler\IntermediateRepresentation\Type();
 * $typeInt->name = 'int';
 *
 * $input1 = new Kitab\Compiler\IntermediateRepresentation\Parameter('x');
 * $input1->type = $typeInt;
 *
 * $output = $typeInt;
 *
 * $function = new Kitab\Compiler\IntermediateRepresentation\Function_('f');
 * $function->inputs[] = $input1;
 * $function->output   = $output;
 * ```
 */
class Function_ extends Entity
{
    /**
     * Type of the entity. See parent.
     */
    const TYPE = 'function';

    /**
     * Collection of `Kitab\Compiler\IntermediateRepresentation\Parameter`
     * instances.
     */
    public $inputs = [];

    /**
     * An output is a `Kitab\Compiler\IntermediateRepresentation\Type`
     * instance if any.
     */
    public $output = null;

    /**
     * Allocate a new named function with a name. This is the only mandatory
     * information.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
