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

interface NewsletterAwareCustomerInterface extends CustomerInterface
{
    /**
     * @return bool
     */
    public function getNewsletter();

    /**
     * @param bool $newsletter
     */
    public function setNewsletter($newsletter);

    /**
     * @param \Carbon\Carbon $newsletterUnsubscrriptionDate
     * @return \Pimcore\Model\Object\Customer
     */
    public function setNewsletterUnsubscriptionDate ($newsletterUnsubscrriptionDate);

    /**
     * Get newsletterUnsubscrriptionDate - Unsubscription date
     * @return \Carbon\Carbon
     */
    public function getNewsletterUnsubscriptionDate ();

    /**
     * @param \Carbon\Carbon $newsletterUnsubscrriptionDate
     * @return \Pimcore\Model\Object\Customer
     */
    public function setNewsletterUnsubscrriptionDate ($newsletterUnsubscrriptionDate);

    /**
     * @param string $newsletterDataMd5
     * @return \Pimcore\Model\Object\Customer
     */
    public function setNewsletterDataMd5 ($newsletterDataMd5);
}
