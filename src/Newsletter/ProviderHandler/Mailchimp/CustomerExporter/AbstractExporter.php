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

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\CustomerExporter;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\MailChimpExportService;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;
use CustomerManagementFrameworkBundle\Traits\ApplicationLoggerAware;
use DrewM\MailChimp\MailChimp;
use Pimcore\Model\Element\ElementInterface;

abstract class AbstractExporter
{
    use ApplicationLoggerAware;

    /**
     * @var MailChimpExportService
     */
    protected $exportService;

    /**
     * @var MailChimp
     */
    protected $apiClient;

    /**
     * @var NewsletterQueueInterface
     */
    protected $newsletterQueue;

    /**
     * AbstractExporter constructor.
     *
     * @param MailChimpExportService $interpreter
     */
    public function __construct(MailChimpExportService $exportService, NewsletterQueueInterface $newsletterQueue)
    {
        $this->exportService = $exportService;
        $this->apiClient = $exportService->getApiClient();
        $this->newsletterQueue = $newsletterQueue;
        $this->setLoggerComponent('NewsletterSync');
    }

    /**
     * @param int $id
     *
     * @return CustomerInterface|ElementInterface|null
     */
    protected function getCustomer($id)
    {
        return \Pimcore::getContainer()
            ->get('cmf.customer_provider')
            ->getById($id);
    }
}
