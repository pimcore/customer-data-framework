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

class De implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        preg_match('/\\b\\d{4,5}\\b/', $data, $matches);

        if ($match = $matches[0]) {
            if (strlen($match) == 4 && !(strpos($match, '0') === 0)) {
                return '0'.$match;
            }

            return $match;
        }

        return $data;
    }
}
