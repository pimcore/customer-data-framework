<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
