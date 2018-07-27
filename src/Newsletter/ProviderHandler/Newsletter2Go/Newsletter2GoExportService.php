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
            if($newsletter2GoProviderHandler->getNewsletterStatus($customer) == 'unsubscribed' || $item->getOperation() == NewsletterQueueInterface::OPERATION_DELETE) {
                $deleteItems[] = $item;
            } else if($newsletter2GoProviderHandler->getNewsletterStatus($customer) == 'subscribed' ) {
                $updateItems[] = $item;
            } else {
                $item->setSuccessfullyProcessed(true);
            }
        }


        if(count($deleteItems)) {
            $this->deleteMultiple($items, $newsletter2GoProviderHandler);
        }

        if(count($updateItems)) {
            $this->updateMultiple($items, $newsletter2GoProviderHandler);
        }
    }





    protected function buildCustomerParams(NewsletterQueueItemInterface $item, Newsletter2Go $newsletter2GoProviderHandler) {
        $customer = $item->getCustomer();

        $data = [];

        $data['list_id'] = $newsletter2GoProviderHandler->getListId();
        $data['email'] = $customer->getEmail();
        $data['phone'] = $customer->getPhone();
        if($gender = $customer->getGender()) {
            if($gender == 'male') {
                $data['gender'] = 'm';
            } else if($gender == 'female') {
                $data['gender'] = 'f';
            }
        }
        $data['first_name'] = $customer->getFirstname();
        $data['last_name'] = $customer->getLastname();


        return $data;
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
                $data[] = $this->buildCustomerParams($updateItem, $newsletter2GoProviderHandler);
            }

            $endpoint = '/recipients';
            $response = $this->newsletter2GoRESTApi->curl($endpoint, $data, 'POST');



            //how the heck should we match to an id here? another req getting the data??
            //todo we need to check if it was successfull...
            foreach($items as $item) {
                $customer = $item->getCustomer();
                $newsletter2GoProviderHandler->updateNewsletter2GoStatus($customer, $newsletter2GoProviderHandler->mapNewsletterStatus('subscribed'));
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
                $customer = $item->getCustomer();
                $item->setSuccessfullyProcessed(true);
                $newsletter2GoProviderHandler->updateNewsletter2GoStatus($customer, '');
                $newsletter2GoProviderHandler->updateNewsletterStatus($customer, '');
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
        $params['recipient'] = $this->buildCustomerParams($item, $newsletter2GoProviderHandler);
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