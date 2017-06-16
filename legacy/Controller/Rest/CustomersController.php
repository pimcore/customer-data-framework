<?php

namespace CustomerManagementFrameworkBundle\Controller\Rest;

use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\RESTApi\CustomersHandler;

class CustomersController extends RestHandlerController
{
    /**
     * @return CustomersHandler
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiCustomersHandler();
    }
}
