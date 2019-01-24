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
use Pimcore\DataObject\Import\Service as ImportService;
use Pimcore\Tool\Admin;

class CustomerImportService  {

    /**
     * @var ImportService
     */
    protected $importService;

    /**
     * @var CustomerProviderInterface $customerProvider
     */
    protected $customerProvider;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * @return bool
     */
    public function isCustomerImportAllowed(): bool
    {
        $id = \Pimcore::getContainer()->getParameter('pimcore_customer_management_framework.import.customerImporterId');
        $pimcoreClassId = \Pimcore::getContainer()->get(CustomerProviderInterface::class)->getCustomerClassId();

        return $this->isImporterIdAllowed($id, $pimcoreClassId);
    }

    /**
     * @param int $importerId
     * @param mixed|null $pimcoreClassId
     * @return bool
     */
    public function isImporterIdAllowed(int $importerId, $pimcoreClassId = null): bool
    {
        if(!$importerId) {
            return false;
        }

        if(is_null($pimcoreClassId)) {
            return false;
        }

        if(!$user = Admin::getCurrentUser()) {
            return false;
        }

        $importConfigs = array_merge(
            (array)$this->importService->getMyOwnImportConfigs($user, $pimcoreClassId),
            (array)$this->importService->getSharedImportConfigs($user, $pimcoreClassId)
        );

        foreach ($importConfigs as $config) {
            if($config->getId() == $importerId) {
                return true;
            }
        }

        return false;
    }
}