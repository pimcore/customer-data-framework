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

use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;

class NewsletterTerminateListener
{
    /**
     * @var NewsletterQueueInterface
     */
    protected $newsletterQueue;

    public function __construct(NewsletterQueueInterface $newsletterQueue)
    {
        $this->newsletterQueue = $newsletterQueue;
    }

    public function onTerminate()
    {
        $this->newsletterQueue->executeImmidiateAsyncQueueItems();
    }
}
