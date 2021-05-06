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

class Se implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        preg_match('/\\b\\d{3} {0,1}\\d{2}\\b/', $data, $matches);

        $result = $data;
        if ($match = ($matches[0] ?? 0)) {
            if (strlen($match) == 5) {
                $result = substr($match, 0, 3).' '.substr($match, 3);
            } else {
                $result = $match;
            }
        }

        return strtoupper($result);
    }
}
