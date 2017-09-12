<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\MailchimpAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;
use Pimcore\Model\User;
use Psr\Log\LoggerInterface;

class UpdateFromMailchimpProcessor
{

    /**
     * @var User
     */
    private $user;

    public function __construct()
    {
        $resolver = \Pimcore::getContainer()->get('pimcore_admin.security.token_storage_user_resolver');
        $this->user = $resolver->getUser();
    }

    /**
     * Updates the customer instance with the given mailchimpStatus.
     * This updates the instance only but applies no save of the customer!
     * Returns true if any status changed during the method.
     *
     * @param Mailchimp $mailchimpHandler
     * @param MailchimpAwareCustomerInterface $mailchimpAwareCustomer
     * @param $status
     * @return bool
     */
    public function updateNewsletterStatus(Mailchimp $mailchimpHandler, MailchimpAwareCustomerInterface $customer, $mailchimpStatus)
    {

        $changed = false;

        if($newsletterStatus = $mailchimpHandler->reverseMapNewsletterStatus($mailchimpStatus)) {
            if(!$changed && ($customer->getNewsletterStatus() != $newsletterStatus)) {
                $changed = true;
            }
            $customer->setNewsletterStatus($newsletterStatus);
        }
        if($mailchimpHandler->getMailchimpStatus($customer) != $mailchimpStatus) {
            $mailchimpHandler->setMailchimpStatus($customer, $mailchimpStatus);
            $changed = true;
        }

        return $changed;

    }

    /**
     * @param Mailchimp $mailchimpHandler
     * @param MailchimpAwareCustomerInterface $customer
     * @param array $mergeFieldData
     * @param LoggerInterface $logger
     * @return bool
     */
    public function processMergeFields(Mailchimp $mailchimpHandler, MailchimpAwareCustomerInterface $customer, array $mergeFieldData)
    {
        $changed = false;
        foreach($mergeFieldData as $key => $value) {
            if($reverseMapped = $mailchimpHandler->reverseMapMergeField($key, $value)) {

                $setter = 'set' . ucfirst($reverseMapped['field']);
                $getter = 'get' . ucfirst($reverseMapped['field']);

                $currentPimcoreData = $customer->$getter();


                if($mailchimpHandler->didMergeFieldDataChange($reverseMapped['field'], $currentPimcoreData, $value)) {
                    $changed = true;
                    $customer->$setter($reverseMapped['value']);
                }
            }
        }

        return $changed;
    }

    public function saveCustomerIfChanged(MailchimpAwareCustomerInterface $customer, $changed)
    {
        if($changed) {
            if($this->user) {
                $customer->setUserModification($this->user->getId());
            }

            $customer->saveWithOptions(
                $customer->getSaveManager()->getSaveOptions(true)
                    ->disableValidator()
                    ->disableNewsletterQueue()
            );
        }
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }


}