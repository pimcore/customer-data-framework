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

namespace CustomerManagementFrameworkBundle\CustomerView\Customer;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Customer;
use Pimcore\Model\DataObject\Service;

class Maintenance
{
    use LoggerAware;

    /**
     * Cleans temporary customers which are not saved from the temporary customers folder defined in configuration key
     * 'pimcore_customer_management_framework.customer_provider.newCustomersTempDir'
     */
    public function cleanUpTemporaryCustomers()
    {
        AbstractObject::setHideUnpublished(false);

        $this->getLogger()->info('Start cleanup for temporary customer objects.');
        // counter for deleted customers
        $changedCounter = 0;
        // fetch customer temp directory
        $tempCustomerPath = \Pimcore::getContainer()->getParameter('pimcore_customer_management_framework.customer_provider.newCustomersTempDir');
        $folder = Service::createFolderByPath($tempCustomerPath);
        // fetch customers of folder
        /** @var Customer[] $tempCustomers */
        $tempCustomers = $folder->getChildren();

        $this->getLogger()->info('Found temporary customer objects: ' . count($tempCustomers));

        // check each customer if it should be deleted
        foreach ($tempCustomers as $customer) {
            // fetch modification date
            $date = Carbon::createFromTimestamp($customer->getModificationDate());
            // if contact is unpublished and last modification was more then 1 day ago
            if (!$customer->isPublished() && $date->diffInDays(Carbon::now()) > 1) {
                // delete the customer
                $customer->delete();
                $changedCounter++;
            }
        }

        $this->getLogger()->info('Cleaned temporary customer objects: ' . $changedCounter);
        $this->getLogger()->info('Finished cleanup!');
    }
}
