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

class Be implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        preg_match('/\\b\\d{4}\\b/', $data, $matches);

        if ($match = $matches[0]) {
            return $match;
        }

        return $data;
    }
}
