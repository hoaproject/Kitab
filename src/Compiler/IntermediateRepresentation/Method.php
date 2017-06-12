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
 * A method intermediate representation.
 *
 * A method is a function inside a class. Contrary to a function, it receives
 * an implicit `$this` argument representing the object (class instance),
 * except for static method. Just like a function, it has zero or many inputs,
 * and zero or one output. Each input is represented by a
 * `Kitab\Compiler\IntermediateRepresentation\Parameter` instance, while the
 * output is represented by a `Kitab\Compiler\IntermediateRepresentation\Type`
 * instance. A method also has a visiblity, can be abstract (if the class is
 * abstract), and can be final (cannot be overriden by a child class).
 *
 * # Examples
 *
 * In this example, a new method `f` is created with 1 input: `int $x`, and
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
 * $method = new Kitab\Compiler\IntermediateRepresentation\Method('f');
 * $method->inputs[] = $input1;
 * $method->output   = $output;
 * ```
 */
class Method
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
     * Unsigned integer representing where the method declaration starts in
     * the file. The file can be retrieved on the class containing this
     * method.
     */
    public $lineStart     = 0;

    /**
     * Unsigned integer representing where the entity declaration ends in the
     * file. The file can retrieved on the class containing this method.
     */
    public $lineEnd       = 0;

    /**
     * The visibility of the method, represented by the `self::VISIBILITY_*`
     * constants.
     */
    public $visibility    = self::VISIBILITY_PUBLIC;

    /**
     * Represent whether the method is static or not.
     *
     * A static method does not receive the implicit `$this` argument
     * representing the current object.
     */
    public $static        = false;

    /**
     * Represent whether the method is abstract or not.
     *
     * An abstract method does not an implementation (body), it only provides
     * a signature (inputs and output). If at least one method is abstract,
     * the whole class must be marked as abstract.
     */
    public $abstract      = false;

    /**
     * Represent whether the method is final or not.
     *
     * A final method cannot be overriden in a child class.
     */
    public $final         = false;

    /**
     * The name of the method.
     */
    public $name;

    /**
     * Collection of `Kitab\Compiler\IntermediateRepresentation\Parameter`
     * instances.
     */
    public $inputs        = [];

    /**
     * An output is a `Kitab\Compiler\IntermediateRepresentation\Type`
     * instance if any.
     */
    public $output        = null;

    /**
     * Associated documentation of the entity as an instance of
     * `Kitab\Compiler\IntermediateRepresentation\Documentation`.
     */
    public $documentation = null;

    /**
     * Allocate a new method with a name. This is the only mandatory
     * information.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Transform the intermediate representation into its PHP representation.
     *
     * The original formatting is not kept. The applied formatting is design
     * for Kitab.
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
            '%s%s%s%s function %s%s(%s)%s',
            $this->final ? 'final ' : '',
            $this->abstract ? 'abstract ' : '',
            $visibility,
            $this->static ? ' static' : '',
            $this->output->reference ? '&' : '',
            $this->name,
            implode(', ', $this->inputs),
            $this->output->name
                ? ': ' . ($this->output->nullable ? '?' : '') . $this->output->name
                : ''
        );
    }
}
