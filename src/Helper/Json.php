<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Helper;

class Json
{
    /**
     * @param string $json
     *
     * @return string
     */
    public static function cleanUpJson($json)
    {
        $search = ["\n", "\r", "\u", "\t", "\f", "\b", '/', '"'];
        $replace = ['\\n', '\\r', '\\u', '\\t', '\\f', '\\b', "\/", '"'];

        return str_replace($search, $replace, $json);
    }
}
