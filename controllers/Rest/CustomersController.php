<?php

use CustomerManagementFramework\Controller\Rest\RestHandlerController;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\RESTApi\CustomersHandler;

class CustomerManagementFramework_Rest_CustomersController extends RestHandlerController
{
    /**
     * @return CustomersHandler
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiCustomersHandler();
    }
}
