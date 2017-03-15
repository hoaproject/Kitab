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
use Hoa\Protocol\Protocol;
use Kitab\Compiler\IntermediateRepresentation;
use Kitab\Compiler\Target\Target;
use Kitab\Compiler\Target\Templater;
use Kitab\Exception;
use StdClass;

class Html implements Target
{
    protected $_view = null;

    public function __construct(Router $router = null)
    {
        if (null === $router) {
            $this->_router = new Router();
        }

        return;
    }

    public function compile(IntermediateRepresentation\File $file)
    {
        foreach ($file as $representation) {
            if ($representation instanceof IntermediateRepresentation\Class_) {
                $this->compileClass($representation);
            } elseif ($representation instanceof IntermediateRepresentation\Interface_) {
                $this->compileInterface($representation);
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

        return $this->compileEntity(
            $class,
            __DIR__ . DS . 'Template' . DS . 'Class.html',
            $data
        );
    }

    protected function compileInterface(Intermediaterepresentation\Interface_ $interface)
    {
        $data            = new StdClass();
        $data->interface = $interface;

        return $this->compileEntity(
            $interface,
            __DIR__ . DS . 'Template' . DS . 'Interface.html',
            $data
        );
    }

    protected function compileEntity(Intermediaterepresentation\Entity $entity, string $templateFile, StdClass $data)
    {
        $output =
            'hoa://Kitab/Output/' .
            $this->_router->unroute(
                $entity->getType(),
                [
                    'namespaceName' => mb_strtolower(str_replace('\\', '/', $entity->getNamespaceName())),
                    'shortName'     => $entity->getShortName()
                ]
            );

        $outputDirectory = dirname($output);

        if (false === is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $view = new Templater(new Write($output, Write::MODE_TRUNCATE_WRITE));
        $view->render($templateFile, $data);

        return;
    }

    public function assemble(array $symbols)
    {
        return $this->assembleNamespaces($symbols, '');
    }

    protected function assembleNamespaces(array $symbols, string $accumulator)
    {
        foreach ($symbols as $symbolPrefix => $subSymbols) {
            if ('@' !== $symbolPrefix[0]) {
                $nextAccumulator = $accumulator . $symbolPrefix . '\\';
                $namespaceName   = mb_strtolower(str_replace('\\', '/', $nextAccumulator));

                $output =
                    'hoa://Kitab/Output/' .
                    $this->_router->unroute(
                        'namespace',
                        [
                            'namespaceName' => $namespaceName
                        ]
                    );

                $data = new StdClass();
                $data->namespace       = new StdClass();
                $data->namespace->name = rtrim($nextAccumulator, '\\');

                $data->namespace->namespaces = [];
                $data->namespace->classes    = [];
                $data->namespace->interfaces = [];

                foreach ($subSymbols as $subSymbolPrefix => $subSymbol) {
                    if ('@' !== $subSymbolPrefix[0]) {
                        $_namespace       = new StdClass();
                        $_namespace->name = $subSymbolPrefix;
                        $_namespace->url  =
                            '.' .
                            $this->_router->unroute(
                                'namespace',
                                [
                                    'namespaceName' => $namespaceName . $subSymbolPrefix
                                ]
                            );

                        $data->namespace->namespaces[] = $_namespace;

                        continue;
                    }

                    list($subSymbolType, $subSymbolName) = explode(':', $subSymbolPrefix);

                    switch ($subSymbolType) {
                        case '@class':
                            $_class       = new StdClass();
                            $_class->name = $subSymbolName;
                            $_class->url  =
                                '.' .
                                $this->_router->unroute(
                                    'class',
                                    [
                                        'namespaceName' => rtrim($namespaceName, '/'),
                                        'shortName'     => $subSymbolName
                                    ]
                                );

                            $data->namespace->classes[] = $_class;

                            break;

                        case '@interface':
                            $_interface       = new StdClass();
                            $_interface->name = $subSymbolName;
                            $_interface->url  =
                                '.' .
                                $this->_router->unroute(
                                    'interface',
                                    [
                                        'namespaceName' => rtrim($namespaceName, '/'),
                                        'shortName'     => $subSymbolName
                                    ]
                                );

                            $data->namespace->interfaces[] = $_interface;

                            break;
                    }
                }

                $data->layout = new StdClass();
                $data->layout->base   = './' . str_repeat('../', substr_count($nextAccumulator, '\\'));
                $data->layout->title  = 'Foobar';
                $data->layout->import = __DIR__ . DS . 'Template' . DS . 'Namespace.html';

                $this->_view = new Templater(new Write($output, Write::MODE_TRUNCATE_WRITE));
                $this->_view->render(
                    __DIR__ . DS . 'Template' . DS . 'Layout.html',
                    $data
                );

                $this->assembleNamespaces(
                    $subSymbols,
                    $nextAccumulator
                );
            }
        }
    }
}
