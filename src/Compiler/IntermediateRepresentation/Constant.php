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
 * A constant intermediate representation.
 *
 * A constant is a property of a class entity.
 *
 * # Examples
 *
 * In this example, a new constant `FOO` is built, with a protected
 * visibility, and a value sets to 42.
 *
 * ```php
 * $constant             = new Kitab\Compiler\IntermediateRepresentation\Constant('FOO');
 * $constant->visibility = $constant::VISIBILITY_PROTECTED;
 * $constant->value      = '42';
 *
 * assert('protected const FOO = 42' === (string) $attribute);
 * ```
 */
class Constant
{
    /**
     * Represent a public constant.
     */
    const VISIBILITY_PUBLIC    = 0;

    /**
     * Represent a protected constant.
     */
    const VISIBILITY_PROTECTED = 1;

    /**
     * Represent a private constant.
     */
    const VISIBILITY_PRIVATE   = 2;

    /**
     * The visibility of the attribute, represented by the
     * `self::VISIBILITY_*` constants.
     */
    public $visibility    = self::VISIBILITY_PUBLIC;

    /**
     * Represent the name of the constant.
     */
    public $name;

    /**
     * A string containing only PHP code representing the value of the constant.
     */
    public $value         = null;

    /**
     * Associated documentation of the constant.
     */
    public $documentation = '';

    /**
     * Allocate a constant with a name. This is the only mandatory information.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Transform this intermediate representation into its PHP representation.
     *
     * The original formatting is not kept. The applied formatting is designed for Kitab.
     */
    public function __toString(): string
    {
        switch ($this->visibility) {
            case self::VISIBILITY_PROTECTED:
                $visibility = 'protected';

                break;

            case self::VISIBILITY_PRIVATE:
                $visibility = 'private';

                break;

            default:
                $visibility = 'public';
        }

        return sprintf(
            '%s const %s = %s',
            $visibility,
            $this->name,
            $this->value
        );
    }
}
