<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
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
