<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\DataTransformer\Cleanup;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class Email implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        return trim(filter_var($data, FILTER_SANITIZE_EMAIL), " \t\n\r\0\x0B.-_@'$|");
    }
}
