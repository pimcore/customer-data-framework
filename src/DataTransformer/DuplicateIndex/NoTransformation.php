<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class NoTransformation implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        return $data;
    }
}
