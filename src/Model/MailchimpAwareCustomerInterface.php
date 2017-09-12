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

/**
 * Additionally to the fields of this interface the customer object needs a field with the following name format for each registered mailchimp newsletter provider handler:
 * 'mailchimpStatus' . ucfirst($newsletterProviderShortcut)
 *
 * Interface MailchimpAwareCustomerInterface
 * @package CustomerManagementFrameworkBundle\Model
 */
interface MailchimpAwareCustomerInterface extends NewsletterAwareCustomerInterface
{


}