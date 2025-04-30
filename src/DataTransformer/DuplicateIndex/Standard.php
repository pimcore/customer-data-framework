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

namespace CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class Standard implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        if ($data instanceof \DateTime) {
            $data = $data->format(\DateTimeInterface::ATOM);
        }

        return trim(strtolower(str_replace('  ', ' ', $data)));
    }
}
