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

namespace CustomerManagementFrameworkBundle\GDPR\Consent;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\DataObject\Data\Consent;

class ConsentChecker implements ConsentCheckerInterface
{
    public function hasProfilingConsent(CustomerInterface $customer): bool
    {
        $consent = $customer->getProfilingConsent() instanceof Consent ? $customer->getProfilingConsent()->getConsent() : $customer->getProfilingConsent();

        return (bool) $consent;
    }
}
