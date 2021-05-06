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

namespace CustomerManagementFrameworkBundle\Controller\Rest\Mailchimp;

use CustomerManagementFrameworkBundle\Controller\Rest\AbstractRestController;
use CustomerManagementFrameworkBundle\Newsletter\Manager\NewsletterManagerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;
use DrewM\MailChimp\Webhook;
use Monolog\Handler\StreamHandler;
use Pimcore\Db;
use Pimcore\Log\ApplicationLogger;
use Pimcore\Log\Handler\ApplicationLoggerDb;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractRestController
{
    /**
     * @param Request $request
     * @Route("/mailchimp/webhook", methods={"GET","POST"})
     */
    public function process(Request $request)
    {
        $result = Webhook::receive();

        $logger = $this->createLogger();
        $logger->info('webhook received: ' . ($result ? json_encode($result) : 'false'));

        if ($result) {
            /**
             * @var NewsletterManagerInterface $newsletterManager
             */
            $newsletterManager = $this->container->get(NewsletterManagerInterface::class);

            foreach ($newsletterManager->getNewsletterProviderHandlers() as $newsletterProviderHandler) {
                if ($newsletterProviderHandler instanceof Mailchimp) {
                    $newsletterProviderHandler->processWebhook($result, $logger);
                }
            }
        }

        return new JsonResponse('ok');
    }

    private function createLogger()
    {
        $logger = new ApplicationLogger();
        $logger->setComponent('Mailchimp');
        $dbWriter = new ApplicationLoggerDb(Db::get(), 'notice');
        $logger->addWriter($dbWriter);

        $fileWriter = new StreamHandler(PIMCORE_LOG_DIRECTORY . '/cmf/mailchimp-webhook.log');

        $logger->addWriter($fileWriter);

        return $logger;
    }
}
