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

class Nl implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        preg_match('/\\d{4} {0,1}\\w{2}/', $data, $matches);

        $result = $data;
        if ($match = $matches[0]) {
            if (strlen($match) == 6) {
                $result = substr($match, 0, 4).' '.substr($match, 4);
            } else {
                $result = $match;
            }
        }

        return strtoupper($result);
    }
}
