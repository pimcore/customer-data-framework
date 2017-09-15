<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Controller\Rest\Mailchimp;

use CustomerManagementFrameworkBundle\Newsletter\Manager\NewsletterManagerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;
use DrewM\MailChimp\Webhook;
use Monolog\Handler\StreamHandler;
use Pimcore\Bundle\AdminBundle\Controller\Rest\AbstractRestController;
use Pimcore\Log\ApplicationLogger;
use Pimcore\Log\Handler\ApplicationLoggerDb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractRestController
{
    /**
     * @param Request $request
     * @Route("/mailchimp/webhook")
     * @Method({"GET","POST"})
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
        $dbWriter = new ApplicationLoggerDb('notice');
        $logger->addWriter($dbWriter);

        $fileWriter = new StreamHandler(PIMCORE_LOG_DIRECTORY . '/cmf/mailchimp-webhook.log');

        $logger->addWriter($fileWriter);

        return $logger;
    }
}
