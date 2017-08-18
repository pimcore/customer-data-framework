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

class Street extends Simplify
{
    public function transform($data, $options = [])
    {
        $data = parent::transform($data, $options);
        $data = str_replace(['strasse'], ['str.'], $data);

        return preg_replace('/\str$/', 'str.', $data);
    }
}
