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
 * A parameter intermediate representation.
 *

 * A parameter of a function (or method) receives an argument. It takes the
 * form of a variable with a type and a default value. A parameter can be
 * variadic if it is in the last position of the list of parameters: It means
 * it will receive all extra arguments given to the function.
 *
 * # Examples
 *
 * The following example represent the parameter `int $foo = 42`:
 *
 * ```php
 * $typeInt       = new Kitab\Compiler\IntermediateRepresentation\Type();
 * $typeInt->name = 'int';
 *
 * $parameter        = new Kitab\Compiler\IntermediateRepresentation\Parameter();
 * $parameter->type  = $typeInt;
 * $parameter->name  = 'foo';
 * $parameter->value = '42';
 * ```
 */
class Parameter
{
    /**
     * Type of the parameter. See `Kitab\Compiler\IntermediateRepresentation\Type`.
     */
    public $type     = null;

    /**
     * Name of the parameter, without the leading `$`.
     */
    public $name;

    /**
     * A variadic parameter receives all the extra arguments given to the
     * function. It must be the last parameter of the list of parameters. It
     * is represented by the `...` symbol.
     */
    public $variadic = false;

    /**
     * A string containing only PHP code representing the default value of the parameter if any.
     * A default value for the
     */
    public $value    = null;

    /**
     * Allocate a parameter with a name. This is the only mandatory information.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Transform the intermediate representation into its PHP representation.
     *
     * The original formatting is not kept. The applied formatting is designed
     * for Kitab.
     */
    public function __toString(): string
    {
        return sprintf(
            '%s%s$%s%s',
            $this->type,
            $this->variadic ? '...' : '',
            $this->name,
            $this->value ? ' = ' . $this->value : ''
        );
    }
}
