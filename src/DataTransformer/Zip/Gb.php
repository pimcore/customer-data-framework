<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\DataTransformer\Zip;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class Gb implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        $data = strtoupper($data);

        preg_match(
            '/([A-PR-UWYZ0-9][A-HK-Y0-9][AEHMNPRTVXY0-9]?[ABEHMNPRVWXY0-9]? {0,2}[0-9][ABD-HJLN-UW-Z]{2}|GIR 0AA)/',
            $data,
            $matches
        );

        if ($match = ($matches[0] ?? 0)) {
            if (strpos($match, ' ') === false && strlen($match) > 4) {
                return substr($match, 0, 3).' '.substr($match, 3);
            } else {
                return $match;
            }
        }

        return $data;
    }
}
