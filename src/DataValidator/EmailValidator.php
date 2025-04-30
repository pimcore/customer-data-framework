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

namespace CustomerManagementFrameworkBundle\DataValidator;

class EmailValidator implements DataValidatorInterface
{
    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function isValid($data)
    {
        return filter_var($data, FILTER_VALIDATE_EMAIL);
    }
}
