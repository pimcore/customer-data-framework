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

class Nl implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        preg_match('/\\d{4} {0,1}\\w{2}/', $data, $matches);

        $result = $data;
        if ($match = ($matches[0] ?? 0)) {
            if (strlen($match) == 6) {
                $result = substr($match, 0, 4).' '.substr($match, 4);
            } else {
                $result = $match;
            }
        }

        return strtoupper($result);
    }
}
