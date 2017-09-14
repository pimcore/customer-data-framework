<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Event\Frontend;

use Pimcore\Bundle\CoreBundle\EventListener\Frontend\AbstractFrontendListener;
use Pimcore\Bundle\CoreBundle\EventListener\Traits\PimcoreContextAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UrlActivityTracker implements EventSubscriberInterface
{
    use PimcoreContextAwareTrait;

    protected $allreadyTracked = false;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

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

        if ($this->allreadyTracked) {
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

        $this->allreadyTracked = true;
    }
}
