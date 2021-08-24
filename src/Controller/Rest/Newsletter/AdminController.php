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

namespace CustomerManagementFrameworkBundle\Controller\Rest\Newsletter;

use CustomerManagementFrameworkBundle\Controller\Rest\AbstractRestController;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;
use Pimcore\Tool\Console;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractRestController
{
    /**
     * @Route("/newsletter/enqueue-all-customers", methods={"GET"})
     */
    public function enqueueAllCustomers()
    {
        $php = Console::getExecutable('php');
        Console::execInBackground($php . ' ' . PIMCORE_PROJECT_ROOT . '/bin/console cmf:newsletter-sync --enqueue-all-customers -c');

        return new JsonResponse('ok');
    }

    /**
     * @Route("/newsletter/get-queue-size", methods={"GET"})
     */
    public function getQueueSize(NewsletterQueueInterface $queue)
    {
        return new JsonResponse(['size' => $queue->getQueueSize()]);
    }
}
