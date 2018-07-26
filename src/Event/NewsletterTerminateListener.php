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

use CustomerManagementFrameworkBundle\ActionTrigger\Event\TargetGroupAssigned;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;
use CustomerManagementFrameworkBundle\Targeting\DataProvider\Customer;
use Pimcore\Event\Targeting\AssignDocumentTargetGroupEvent;
use Pimcore\Event\Targeting\TargetingRuleEvent;
use Pimcore\Logger;
use Pimcore\Model\Tool\Targeting\TargetGroup;
use Pimcore\Targeting\DataLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
