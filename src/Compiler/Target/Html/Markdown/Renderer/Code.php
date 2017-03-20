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

namespace Kitab\Compiler\Target\Html\Markdown\Renderer;

use Kitab\Compiler\Target\Html\Router;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Code as CodeElement;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

class Code implements InlineRendererInterface
{
    protected $_router = null;

    public function __construct(Router $router)
    {
        $this->_router = $router;

        return;
    }

    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        if (!($inline instanceof CodeElement)) {
            throw new \InvalidArgumentException('Incompatible inline type: ' . get_class($inline));
        }

        $content = $inline->getContent();

        if (0 == preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x80-\xff]*(\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x80-\xff]*)+$/', $content)) {
            return new HtmlElement(
                'code',
                [],
                $content
            );
        }

        $url     = '.';

        list($namespaceName, $shortName) = Router::splitEntityName($content);
        $namespaceName = mb_strtolower(str_replace('\\', '/', $namespaceName));

        if (empty($shortName)) {
            $url .= $this->_router->unroute(
                'namespace',
                [
                    'namespaceName' => $namespaceName
                ]
            );
        } else {
            $url .= $this->_router->unroute(
                'entity',
                [
                    'namespaceName' => $namespaceName,
                    'shortName'     => $shortName
                ]
            );
        }

        return new HtmlElement(
            'a',
            ['href' => $url],
            new HtmlElement(
                'code',
                [],
                $content
            )
        );
    }
}
