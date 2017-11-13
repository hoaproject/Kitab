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

namespace Kitab\Compiler\Target\Html;

use Kitab;

/**
 * Configuration structure for the HTML target.
 *
 * This structure contains all the configuration items used by the HTML target
 * of Kitab. It extends the default Kitab configuration structure.
 *
 *
 * # Examples
 *
 * ```php
 * $configuration              = new Kitab\Compiler\Target\Html\Configuration();
 * $configuration->projectName = 'Kitab';
 *
 * assert('Kitab' === $configuration->projectName);
 * ```
 */
class Configuration extends Kitab\Configuration
{
    /**
     * The default namespace of the project currently documented.
     *
     * A project can contain several namespaces, either because it hosts many,
     * or because of the dependencies. The default namespace is the entry
     * point of the documentation. If a default namespace is provided, when
     * the user will open the documentation, she will be automatically
     * redirected to the default namespace.
     */
    public $defaultNamespace = null;

    /**
     * URL to the logo of the documentation.
     *
     * Each page contains a logo, representing a link to the “home”. It is
     * possible to customise the logo by using this configuration item. By
     * default, a placeholder is used.
     */
    public $logoURL          = 'https://placehold.it/150x150';

    /**
     * Project name.
     *
     * This configuration item represents the name of the project being documented.
     */
    public $projectName      = '(unknown)';

    /**
     * Composer file.
     *
     * Use a specific Composer file to get PSR-4 mappings.
     */
    public $composerFile     = null;
}
