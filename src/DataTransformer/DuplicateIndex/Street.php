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

class Street extends Simplify
{
    public function transform($data, $options = [])
    {
        $data = parent::transform($data, $options);
        $data = str_replace(['strasse'], ['str.'], $data);

        return preg_replace('/\str$/', 'str.', $data);
    }
}
