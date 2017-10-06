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
