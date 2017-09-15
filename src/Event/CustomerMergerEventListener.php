<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Event;

class CustomerMergerEventListener
{
    public function onPostMerge(\Symfony\Component\EventDispatcher\GenericEvent $e)
    {
        $sourceCustomer = \Pimcore::getContainer()->get('cmf.customer_provider')->getById($e->getArgument('sourceId'));
        $targetCustomer = \Pimcore::getContainer()->get('cmf.customer_provider')->getById($e->getArgument('targetId'));

        if ($sourceCustomer && $targetCustomer) {
            \Pimcore::getContainer()->get('cmf.customer_merger')->mergeCustomers($sourceCustomer, $targetCustomer, false);
        }
    }
}
