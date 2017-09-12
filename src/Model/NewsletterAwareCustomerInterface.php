<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Model;

use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterProviderHandlerInterface;

interface NewsletterAwareCustomerInterface extends CustomerInterface
{
    /**
     * If this method returns true the customer will be exported by the provider handler with the given shortcut.
     * Otherwise the provider handler will delete the customer in the target system if it exists.
     * To ensure a consistant handling inactive or unpublished customers should never be exported.
     *
     * @param NewsletterProviderHandlerInterface $newsletterProviderHandler
     * @return bool
     */
    public function needsExportByNewsletterProviderHandler(NewsletterProviderHandlerInterface $newsletterProviderHandler);
}
