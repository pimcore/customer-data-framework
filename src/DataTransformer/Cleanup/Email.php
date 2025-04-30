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

namespace CustomerManagementFrameworkBundle\DataTransformer\Cleanup;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class Email implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        return trim(filter_var($data, FILTER_SANITIZE_EMAIL), " \t\n\r\0\x0B.-_@'$|");
    }
}
