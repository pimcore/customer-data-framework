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
