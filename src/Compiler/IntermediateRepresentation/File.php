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
 * A file intermediate representation.
 *
 * A file is one root containing all the other intermediate
 * representations. By design, it extends the `ArrayObject` class, thus this
 * is a collection of objects.
 *
 * # Examples
 *
 * This example shows how to create a file and to add intermediate
 * representations in it, here a class.
 *
 * ```php
 * $file   = new Kitab\Compiler\IntermediateRepresentation\File('example.php');
 * $file[] = new Kitab\Compiler\IntermediateRepresentation\Class_('C');
 * ```
 */
class File extends \ArrayObject
{
    /**
     * Name of the file.
     */
    public $name;

    /**
     * Allocate a file with a name.
     */
    public function __construct(string $name)
    {
        parent::__construct();

        $this->name = $name;
    }

    /**
     * Automatically propagate this instance onto intermediate representations
     * of kind `Kitab\Compiler\IntermediateRepresentation\Entity` when setting
     * a new pair into the collection.
     * This is a handy way to ensure all the entities receive this instance.
     */
    public function offsetSet($name, $value)
    {
        if ($value instanceof Entity) {
            $value->file = $this;
        }

        return parent::offsetSet($name, $value);
    }
}
