<?php

use CustomerManagementFramework\Controller\Rest\RestHandlerController;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\RESTApi\CustomersHandler;

class CustomerManagementFramework_Rest_DeletionsController extends RestHandlerController
{
    /**
     * @return \CustomerManagementFramework\RESTApi\DeletionsHandler
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiDeletionsHandler();
    }
}
