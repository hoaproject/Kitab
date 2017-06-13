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

namespace Kitab\Compiler\Target;

use Hoa\Router\Router;
use Hoa\Stream\IStream\Out;
use Hoa\View\Viewable;
use StdClass;

class Templater implements Viewable
{
    protected $_in     = null;
    protected $_out    = null;
    protected $_data   = null;
    protected $_router = null;

    public function __construct(
        string   $in,
        Out      $out,
        Router   $router = null,
        StdClass $data = null
    ) {
        if (null === $data) {
            $data = new StdClass();
        }

        $this->_in     = $in;
        $this->_out    = $out;
        $this->_data   = $data;
        $this->_router = $router;

        return;
    }
    public function getOutputStream(): Out
    {
        return $this->_out;
    }

    public function getData(): ?StdClass
    {
        return $this->_data;
    }

    public function render()
    {
        $data   = $this->getData();
        $router = $this->getRouter();

        ob_start();
        require $this->_in;
        $content = ob_get_contents();
        ob_end_clean();

        $this->getOutputStream()->writeAll($content);

        return;
    }

    public function getRouter(): ?Router
    {
        return $this->_router;
    }

    public function import(string $template, StdClass $data = null)
    {
        $router = $this->getRouter();

        require $template;

        return;
    }
}
