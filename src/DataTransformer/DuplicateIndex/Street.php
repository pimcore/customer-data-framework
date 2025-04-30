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

class Street extends Simplify
{
    public function transform($data, $options = [])
    {
        $data = parent::transform($data, $options);
        $data = str_replace(['strasse'], ['str.'], $data);

        return preg_replace('/\str$/', 'str.', $data);
    }
}
