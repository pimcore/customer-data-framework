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

/**
 * Additionally to the fields of this interface the customer object needs a field with the following name format for each registered mailchimp newsletter provider handler:
 * 'mailchimpStatus' . ucfirst($newsletterProviderShortcut)
 *
 * Interface MailchimpAwareCustomerInterface
 *
 * @package CustomerManagementFrameworkBundle\Model
 */
interface MailchimpAwareCustomerInterface extends NewsletterAwareCustomerInterface
{
}
