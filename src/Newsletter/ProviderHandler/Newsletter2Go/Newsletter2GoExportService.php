<?php

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Newsletter2Go;

use CustomerManagementFrameworkBundle\Model\NewsletterAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Newsletter2Go;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\NewsletterQueueItemInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;

class Newsletter2GoExportService
{
    protected $newsletter2GoRESTApi;

    public function __construct(\NL2GO\Newsletter2Go_REST_Api $newsletter2GoRESTApi)
    {
        $this->newsletter2GoRESTApi = $newsletter2GoRESTApi;
    }




    public function export(NewsletterQueueItemInterface $item, Newsletter2Go $newsletter2GoProviderHandler) {
        return $this->exportMultiple([$item], $newsletter2GoProviderHandler);
    }


    /**
     * @param NewsletterQueueItemInterface[] $items
     * @param Newsletter2Go $newsletter2GoProviderHandler
     */
    public function exportMultiple($items, Newsletter2Go $newsletter2GoProviderHandler) {

        $updateItems = [];
        $deleteItems = [];

        foreach($items as $item) {
            $customer = $item->getCustomer();

            if($customer) {
                if ($newsletter2GoProviderHandler->getNewsletterStatus($customer) == 'unsubscribed' || $item->getOperation() == NewsletterQueueInterface::OPERATION_DELETE) {
                    $deleteItems[] = $item;
                } else if ($newsletter2GoProviderHandler->getNewsletterStatus($customer) == 'subscribed') {
                    $updateItems[] = $item;
                } else {
                    $item->setSuccessfullyProcessed(true);
                }
            } else {
                $item->setSuccessfullyProcessed(true);
            }
        }

        if(count($deleteItems)) {
            $this->deleteMultiple($items, $newsletter2GoProviderHandler);
        }

        if(count($updateItems)) {
            foreach($updateItems as $updateItem) {
                $this->update($updateItem, $newsletter2GoProviderHandler);
            }
        }
    }


    /**
     * @param NewsletterQueueItemInterface $item
     * @param Newsletter2Go $newsletter2GoProviderHandler
     * @throws \Exception
     */
    public function update(NewsletterQueueItemInterface $item, Newsletter2Go $newsletter2GoProviderHandler) {
        return $this->updateMultiple([$item], $newsletter2GoProviderHandler);
    }


    /**
     * @param NewsletterQueueItemInterface[] $items
     * @param Newsletter2Go $newsletter2GoProviderHandler
     */
    public function updateMultiple($items, Newsletter2Go $newsletter2GoProviderHandler) {

        if(count($items)) {
            $data = [];
            foreach($items as $updateItem) {
                if($updateItem->getCustomer()) {
                    $data[] = $newsletter2GoProviderHandler->buildEntry($updateItem);
                }
            }


            $endpoint = '/recipients';
            $response = $this->newsletter2GoRESTApi->curl($endpoint, $data, 'POST');

            foreach($items as $item) {
                if($customer = $item->getCustomer()) {
                    $newsletter2GoProviderHandler->updateNewsletter2GoStatus($customer, $newsletter2GoProviderHandler->mapNewsletterStatus('subscribed'));
                    $newsletter2GoProviderHandler->updateNewsletterStatus($customer, 'subscribed');
                }
                $item->setSuccessfullyProcessed(true);
            }
        }

    }



    public function delete(NewsletterQueueItemInterface $item, Newsletter2Go $newsletter2GoProviderHandler) {
        return $this->deleteMultiple([$item], $newsletter2GoProviderHandler);
    }


    /**
     * @param NewsletterQueueItemInterface[] $items
     * @param Newsletter2Go $newsletter2GoProviderHandler
     *
     * @throws
     */
    public function deleteMultiple($items, Newsletter2Go $newsletter2GoProviderHandler) {
        $endpoint = '/lists/'. $newsletter2GoProviderHandler->getListId() . '/recipients';

        $emails = [];
        foreach($items as $item) {
            $emails[] = sprintf('email=="%s"', $item->getEmail());
        }

        if(count($emails)) {
            $data['_filter'] = implode(',', $emails);

            $response = $this->newsletter2GoRESTApi->curl($endpoint, $data, 'DELETE');

            //todo find a better way for this...
            foreach($items as $item) {
                if($customer = $item->getCustomer()) {
                    $newsletter2GoProviderHandler->updateNewsletter2GoStatus($customer, 'unsubscribed');
                    $newsletter2GoProviderHandler->updateNewsletterStatus($customer, 'unsubscribed');
                }
                $item->setSuccessfullyProcessed(true);
            }
        }

    }


    public function getExternalId(NewsletterAwareCustomerInterface $customer, Newsletter2Go $newsletter2GoProviderHandler) {
        $data = $this->getExternalData($customer, $newsletter2GoProviderHandler);

        return $data->id;
    }


    public function getExternalData(NewsletterAwareCustomerInterface $customer, Newsletter2Go $newsletter2GoProviderHandler) {

        if($data = $this->getExternalDataMultiple([$customer])[0]) {
            return $data;
        }
        return null;
    }



    public function getExternalDataMultiple($customers, Newsletter2Go $newsletter2GoProviderHandler) {


        $listId = $newsletter2GoProviderHandler->getListId();
        $endpoint = '/lists/'. $listId .'/recipients';

        $emails = [];
        foreach($customers as $customer) {
            $emails[] = sprintf('email=="%s"', $customer->getEmail());
        }


        $data = [
            '_filter' => implode(',', $emails),
            '_expand' => true
        ];

        $response = $this->newsletter2GoRESTApi->curl($endpoint, $data, 'GET');

        if($response->status == 200) {
            return $response->value;
        }
    }



    public function register(NewsletterQueueItemInterface $item,  Newsletter2Go $newsletter2GoProviderHandler) {

        $customer = $item->getCustomer();
        $params['recipient'] = $newsletter2GoProviderHandler->buildEntry($item);
        unset($params['recipient']['list_id']);


        $formCode = $newsletter2GoProviderHandler->getDoubleOptInFormCode();
        $endpoint = '/forms/submit/'.$formCode;

        $response = $this->newsletter2GoRESTApi->curl($endpoint, $params, 'POST');

        if($response->status == 201) {
            $newsletter2GoProviderHandler->updateNewsletterStatus($customer, 'pending');
            $newsletter2GoProviderHandler->updateNewsletter2GoStatus($customer, $newsletter2GoProviderHandler->mapNewsletterStatus('pending'));
            return true;
        }

        return false;
    }

}