<?php

use CustomerManagementFramework\Controller\Rest\CrudController;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\RESTApi\CrudInterface;
use CustomerManagementFramework\RESTApi\CustomersApi;

class CustomerManagementFramework_Rest_CustomersController extends CrudController
{
    /**
     * @return CrudInterface|CustomersApi
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiCustomers();
    }
}
