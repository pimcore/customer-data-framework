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

namespace CustomerManagementFrameworkBundle\Event\Frontend;

use Pimcore\Bundle\CoreBundle\EventListener\Traits\PimcoreContextAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UrlActivityTracker implements EventSubscriberInterface
{
    use PimcoreContextAwareTrait;

    protected $allreadyTracked = false;

    /**
     * @inheritDoc
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents()//: array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    /**
     * Checks for request params cmfa + cmfc and tracks activity if needed
     *
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (!\Pimcore::getContainer()->getParameter('pimcore_customer_management_framework.url_activity_tracker.enabled')) {
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
