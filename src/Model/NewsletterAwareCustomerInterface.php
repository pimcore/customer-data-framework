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

namespace CustomerManagementFrameworkBundle\Model;

use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterProviderHandlerInterface;

interface NewsletterAwareCustomerInterface extends CustomerInterface
{
    /**
     * If this method returns true the customer will be exported by the provider handler with the given shortcut.
     * Otherwise the provider handler will delete the customer in the target system if it exists.
     * To ensure a consistent handling inactive or unpublished customers should never be exported.
     *
     * @param NewsletterProviderHandlerInterface $newsletterProviderHandler
     *
     * @return bool
     */
    public function needsExportByNewsletterProviderHandler(NewsletterProviderHandlerInterface $newsletterProviderHandler);
}
