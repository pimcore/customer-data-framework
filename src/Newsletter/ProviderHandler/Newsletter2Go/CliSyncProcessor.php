<?php
/**
 * Created by PhpStorm.
 * User: tmittendorfer
 * Date: 18.07.2018
 * Time: 12:53
 */

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Newsletter2Go;


use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Newsletter\Manager\NewsletterManagerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Newsletter2Go;

class CliSyncProcessor
{

    /** @var NewsletterManagerInterface */
    protected $newsletterManager;

    /** @var CustomerProviderInterface */
    protected $customerProvider;

    /** @var Newsletter2Go\Newsletter2GoExportService */
    protected $exportService;

    public function __construct(CustomerProviderInterface $customerProvider, NewsletterManagerInterface $newsletterManager, Newsletter2Go\Newsletter2GoExportService $exportService)
    {
        $this->newsletterManager = $newsletterManager;
        $this->customerProvider = $customerProvider;
        $this->exportService = $exportService;
    }


    public function syncStatusChanges() {

        foreach ($this->newsletterManager->getNewsletterProviderHandlers() as $newsletterProviderHandler) {
            if ($newsletterProviderHandler instanceof Newsletter2Go) {

                $remoteStatusField = $newsletterProviderHandler->getNewsletter2GoStatusFieldName();
                $list = $this->customerProvider->getList();

                $condition = "$remoteStatusField = 'subscribed' OR $remoteStatusField = 'pending'";
                $list->addConditionParam($condition);


                $customerChunk = [];
                foreach($list as $customer) {
                    $customerChunk[$customer->getEmail()] = $customer;

                    if(count($customerChunk) >= 10) {
                        $this->proccessCustomerChunk($customerChunk, $newsletterProviderHandler);

                        $customerChunk = [];
                        \Pimcore::collectGarbage();
                    }
                }
                $this->proccessCustomerChunk($customerChunk, $newsletterProviderHandler);


            }
        }
    }


    //add them to the processing queue and the next sync will handle this
    protected function proccessCustomerChunk($customerChunk, Newsletter2Go $newsletterProviderHandler) {
        $externalRecords = $this->exportService->getExternalDataMultiple($customerChunk, $newsletterProviderHandler);

        $externalEmails = [];
        foreach($externalRecords?:[] as $externalRecord) {
            $externalEmails[] = $externalRecord->email;
        }

        foreach($externalRecords?:[] as $externalRecord) {

            $customer = $customerChunk[$externalRecord->email];
            if($customer) {
                //subscribe if double opt in was accepted
                if($newsletterProviderHandler->getNewsletterStatus($customer) == 'pending') {
                    $newsletterProviderHandler->setNewsletterStatus($customer, 'subscribed');
                    $newsletterProviderHandler->setNewsletter2GoStatus($customer, $newsletterProviderHandler->mapNewsletterStatus('subscribed'));
                    $customer->save();
                }

                //unsubscribe if was unsubscribed
                if($externalRecord->is_globally_unsubscribed == true || $externalRecord->is_unsubscribed == true) {
                    $newsletterProviderHandler->setNewsletterStatus($customer, 'unsubscribed');
                    $newsletterProviderHandler->setNewsletter2GoStatus($customer, $newsletterProviderHandler->mapNewsletterStatus('unsubscribed'));
                    $customer->save();
                }
            }
        }

    }
}