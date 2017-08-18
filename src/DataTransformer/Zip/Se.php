<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\DataTransformer\Zip;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class Se implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        preg_match('/\\b\\d{3} {0,1}\\d{2}\\b/', $data, $matches);

        $result = $data;
        if ($match = $matches[0]) {
            if (strlen($match) == 5) {
                $result = substr($match, 0, 3).' '.substr($match, 3);
            } else {
                $result = $match;
            }
        }

        return strtoupper($result);
    }
}
