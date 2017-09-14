<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\EventListener\Frontend;

use Pimcore\Bundle\CoreBundle\EventListener\Frontend\AbstractFrontendListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class UrlActivityTracker extends AbstractFrontendListener
{
    /**
     * Checks for request params cmfa + cmfc and tracks activity if needed
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if(!\Pimcore::getContainer()->getParameter('pimcore_customer_management_framework.url_activity_tracker.enabled')) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->get('cmfa') || !$request->get('cmfc')) {
            return;
        }

        \Pimcore::getContainer()->get('cmf.activity_url_tracker')->trackActivity(
            $request->get('cmfc'),
            $request->get('cmfa'),
            $request->request->all()
        );
    }
}
