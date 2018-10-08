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

namespace CustomerManagementFrameworkBundle\Import;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use Pimcore\DataObject\Import\Resolver\AbstractResolver;
use CustomerManagementFrameworkBundle\Helper\Objects;

class CustomerImportIdAndEmailResolver extends AbstractResolver {

    public function resolve(\stdClass $config, int $parentId, array $rowData)
    {
        $createOnDemand = $config->resolverSettings->createOnDemand;
        $resolverSettingParams = json_decode($config->resolverSettings->params, true);

        if(!is_array($resolverSettingParams) || !isset($resolverSettingParams['emailColumnIndex'])) {
            throw new \Exception('please provide a valid emailColumnIndex resolver option');
        }

        $idColumn = $this->getIdColumn($config);
        $id = $rowData[$idColumn];
        $email = $rowData[$resolverSettingParams['emailColumnIndex']];

        /**
         * @var CustomerProviderInterface $customerProvider
         */
        $customerProvider = \Pimcore::getContainer()->get(CustomerProviderInterface::class);

        if($customer = $customerProvider->getById($id)) {
            return $customer;
        }

        if($customer = $customerProvider->getActiveCustomerByEmail($email)) {
            return $customer;
        }

        if (!$customer && $createOnDemand) {
            $customer = $customerProvider->createCustomerInstance();
            $customer->setKey('New Customer');
            $customer->setActive(true);
            $customer->setPublished(true);
            $customer->setEmail($email);
            $customer->setParentId($parentId);
            Objects::checkObjectKey($customer);
            $customer->saveDirty();
        }

        if (!$customer) {
            throw new \Exception('failed to resolve customer');
        }

        return $customer;
    }
}