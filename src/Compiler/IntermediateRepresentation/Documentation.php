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
 * A documentation intermediate representation.
 *

 * A documentation is block comment starting with `/**` and “attached” to a
 * statement (like a function, a method, a class, an attribute
 * etc.). “Attached” means that it immediately preceeds a statement, modulo
 * whitespaces.
 *
 * Kitab expects those documentation to contain
 * [CommonMark](http://commonmark.org/) formatted text.
 *
 * The first paragraph of the documentation is called the
 * description. Paragraphes must be separated by 2 consecutive newlines.
 */
class Documentation
{
    /**
     * The raw extracted documentation, with not formatting applied.
     */
    public $documentation = '';

    /**
     * Allocate a documentation.
     */
    public function __construct(string $documentation)
    {
        $this->documentation = $documentation;
    }

    /**
     * The description is the first paragraph of the documentation.
     *
     * It generally gives an overview or the abstract idea of the documented
     * feature. This is more or less like a summary that highlights important
     * points.
     */
    public function getDescription(): string
    {
        if (0 !== preg_match('/\n\n/', $this->documentation, $matches, PREG_OFFSET_CAPTURE)) {
            return substr($this->documentation, 0, $matches[0][1]);
        }

        return $this->documentation;
    }

    /**
     * Transform the intermediate representation.
     *
     * It basically returns the documentation. No extra allocation is applied.
     */
    public function __toString(): string
    {
        return $this->documentation;
    }
}
