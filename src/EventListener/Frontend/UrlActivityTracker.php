<?php

namespace CustomerManagementFrameworkBundle\EventListener\Frontend;

use Pimcore\Bundle\CoreBundle\EventListener\Frontend\AbstractFrontendListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class UrlActivityTracker extends AbstractFrontendListener {

    /**
     * Checks for request params cmfa + cmfc and tracks activity if needed
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if(!$request->get('cmfa') || !$request->get('cmfc')) {
            return;
        }

        \Pimcore::getContainer()->get('cmf.activity_url_tracker')->trackActivity($request->get('cmfc'), $request->get('cmfa'), $request->request->all());
    }
}