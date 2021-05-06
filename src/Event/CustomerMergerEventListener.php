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

namespace CustomerManagementFrameworkBundle\Event;

use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;

class CustomerMergerEventListener
{
    /**
     * @var CustomerSaveManagerInterface
     */
    private $customerSaveManager;

    public function __construct(CustomerSaveManagerInterface $customerSaveManager)
    {
        $this->customerSaveManager = $customerSaveManager;
    }

    public function onPreMerge(\Symfony\Component\EventDispatcher\GenericEvent $e)
    {
        $this->customerSaveManager
            ->getSaveOptions()
            ->disableOnSaveSegmentBuilders()
            ->disableValidator();
    }

    public function onPostMerge(\Symfony\Component\EventDispatcher\GenericEvent $e)
    {
        $sourceCustomer = \Pimcore::getContainer()->get('cmf.customer_provider')->getById($e->getArgument('sourceId'));
        $targetCustomer = \Pimcore::getContainer()->get('cmf.customer_provider')->getById($e->getArgument('targetId'));

        if ($sourceCustomer && $targetCustomer) {
            \Pimcore::getContainer()->get('cmf.customer_merger')->mergeCustomers($sourceCustomer, $targetCustomer, false);
        }
        $this->customerSaveManager->setSaveOptions(
            $this->customerSaveManager->getDefaultSaveOptions()
        );
    }
}
