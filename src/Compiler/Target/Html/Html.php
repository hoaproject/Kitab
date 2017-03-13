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

namespace Kitab\Compiler\Target\Html;

use Hoa\File\Write;
use Kitab\Compiler\IntermediateRepresentation;
use Kitab\Compiler\Target\Target;
use Kitab\Compiler\Target\Templater;
use Kitab\Exception;
use StdClass;

class Html implements Target
{
    protected $_view = null;

    public function __construct()
    {
        $this->_view = new Templater(new Write(1));

        return;
    }

    public function compile(IntermediateRepresentation\File $file)
    {
        foreach ($file as $representation) {
            if ($representation instanceof Intermediaterepresentation\Class_) {
                $this->compileClass($representation);
            } else {
                throw new Exception\TargetUnknownIntermediateRepresentation(
                    'Intermediate representation `%s` has not been handled.',
                    0,
                    get_class($representation)
                );
            }
        }
    }

    protected function compileClass(Intermediaterepresentation\Class_ $class)
    {
        $data        = new StdClass();
        $data->class = $class;

        $this->_view->render(
            __DIR__ . DS . 'Template' . DS . 'Class.html',
            $data
        );

        return;
    }
}
