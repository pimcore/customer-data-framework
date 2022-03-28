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

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\CustomerExporter;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\MailChimpExportService;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;
use CustomerManagementFrameworkBundle\Traits\ApplicationLoggerAware;
use DrewM\MailChimp\MailChimp;

abstract class AbstractExporter
{
    use ApplicationLoggerAware;

    /**
     * @var MailChimpExportService
     */
    protected $exportService;

    /**
     * @var MailChimp|null
     */
    protected $apiClient;

    /**
     * @var NewsletterQueueInterface
     */
    protected $newsletterQueue;

    /**
     * @param NewsletterQueueInterface $newsletterQueue
     */
    public function __construct(NewsletterQueueInterface $newsletterQueue)
    {
        $this->newsletterQueue = $newsletterQueue;
        $this->setLoggerComponent('NewsletterSync');
    }

    /**
     * Get an array containing the HTTP headers and the body of the API response.
     *
     * @return array  Assoc array with keys 'headers' and 'body'
     */
    public function getLastResponse()
    {
        return $this->apiClient ? $this->apiClient->getLastResponse() : [];
    }

    /**
     * @param int $id
     *
     * @return CustomerInterface|null
     */
    protected function getCustomer($id)
    {
        return \Pimcore::getContainer()
            ->get('cmf.customer_provider')
            ->getById($id);
    }

    /**
     * used to be able to track the last response independently of the concrete mailchimp account
     *
     * @param MailChimpExportService $exportService
     *
     * @return MailChimp
     */
    protected function getApiClientFromExportService(MailChimpExportService $exportService): MailChimp
    {
        $this->apiClient = $exportService->getApiClient();

        return $exportService->getApiClient();
    }
}
