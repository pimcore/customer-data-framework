<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
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
