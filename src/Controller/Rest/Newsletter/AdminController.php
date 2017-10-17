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

namespace CustomerManagementFrameworkBundle\Controller\Rest\Newsletter;

use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;
use Pimcore\Bundle\AdminBundle\Controller\Rest\AbstractRestController;
use Pimcore\Tool\Console;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractRestController
{
    /**
     * @Route("/newsletter/enqueue-all-customers")
     * @Method({"GET"})
     */
    public function enqueueAllCustomers()
    {
        $php = Console::getExecutable('php');
        Console::execInBackground($php . ' ' . PIMCORE_PROJECT_ROOT . '/bin/console cmf:newsletter-sync --enqueue-all-customers -c');

        return new JsonResponse('ok');
    }

    /**
     * @Route("/newsletter/get-queue-size")
     * @Method({"GET"})
     */
    public function getQueueSize()
    {
        /**
         * @var NewsletterQueueInterface $queue
         */
        $queue = $this->container->get(NewsletterQueueInterface::class);

        return new JsonResponse(['size' => $queue->getQueueSize()]);
    }
}
