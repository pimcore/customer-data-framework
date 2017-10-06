<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class Standard implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        if ($data instanceof \DateTime) {
            $data = $data->format(\DateTime::ISO8601);
        }

        return trim(strtolower(str_replace('  ', ' ', $data)));
    }
}
