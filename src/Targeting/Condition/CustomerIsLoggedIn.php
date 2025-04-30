<?php

declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Targeting\Condition;

use CustomerManagementFrameworkBundle\Targeting\DataProvider\Customer;
use Pimcore\Bundle\PersonalizationBundle\Targeting\Condition\AbstractVariableCondition;
use Pimcore\Bundle\PersonalizationBundle\Targeting\DataProviderDependentInterface;
use Pimcore\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class CustomerIsLoggedIn extends AbstractVariableCondition implements DataProviderDependentInterface
{
    public static function fromConfig(array $config): self
    {
        return new self();
    }

    public function getDataProviderKeys(): array
    {
        $providers = [
            Customer::PROVIDER_KEY,
        ];

        return $providers;
    }

    public function canMatch(): bool
    {
        return true;
    }

    public function match(VisitorInfo $visitorInfo): bool
    {
        $customer = $visitorInfo->get(Customer::PROVIDER_KEY);

        return !empty($customer);
    }
}
