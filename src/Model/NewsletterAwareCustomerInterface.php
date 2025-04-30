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

namespace CustomerManagementFrameworkBundle\Model;

use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterProviderHandlerInterface;

interface NewsletterAwareCustomerInterface extends CustomerInterface
{
    /**
     * If this method returns true the customer will be exported by the provider handler with the given shortcut.
     * Otherwise the provider handler will delete the customer in the target system if it exists.
     * To ensure a consistent handling inactive or unpublished customers should never be exported.
     *
     *
     * @return bool
     */
    public function needsExportByNewsletterProviderHandler(NewsletterProviderHandlerInterface $newsletterProviderHandler);
}
