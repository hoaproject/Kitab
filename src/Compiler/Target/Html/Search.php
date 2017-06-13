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

use Hoa\File\Directory;
use Hoa\File\Write;

class Search
{
    /**
     * Represent the path to the database file.
     */
    const DATABASE_FILE = 'hoa://Kitab/Output/javascript/search-database.json';

    private static $_database = null;

    public static function insert(array $data)
    {
        static $_id = 0;

        $serializedData = '';

        if (0 < $_id) {
            $serializedData .= ",\n";
        }

        $data['id']      = $_id++ . '';
        $serializedData .= json_encode($data);

        self::getDatabase()->writeAll($serializedData);

        return;
    }

    public static function pack()
    {
        self::getDatabase()->writeAll("\n" . ']');

        return;
    }

    protected static function getDatabase(): Write
    {
        if (null === self::$_database) {
            Directory::create('hoa://Kitab/Output/javascript');

            self::$_database = new Write(
                self::DATABASE_FILE,
                Write::MODE_TRUNCATE_WRITE
            );
            self::$_database->writeAll('[' . "\n");
        }

        return self::$_database;
    }
}
