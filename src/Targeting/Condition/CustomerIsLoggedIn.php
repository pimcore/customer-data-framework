<?php

declare(strict_types=1);

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

namespace CustomerManagementFrameworkBundle\Targeting\Condition;

use CustomerManagementFrameworkBundle\Targeting\DataProvider\Customer;
use Pimcore\Bundle\PersonalizationBundle\Targeting\Condition\AbstractVariableCondition;
use Pimcore\Bundle\PersonalizationBundle\Targeting\DataProviderDependentInterface;
use Pimcore\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class CustomerIsLoggedIn extends AbstractVariableCondition implements DataProviderDependentInterface
{
    /**
     * @inheritDoc
     */
    public static function fromConfig(array $config): self
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public function getDataProviderKeys(): array
    {
        $providers = [
            Customer::PROVIDER_KEY
        ];

        return $providers;
    }

    /**
     * @inheritDoc
     */
    public function canMatch(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function match(VisitorInfo $visitorInfo): bool
    {
        $customer = $visitorInfo->get(Customer::PROVIDER_KEY);

        return !empty($customer);
    }
}
