<?php

use CustomerManagementFramework\Controller\Rest\RestHandlerController;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\RESTApi\CustomersHandler;

class CustomerManagementFramework_Rest_SegmentsController extends RestHandlerController
{
    /**
     * @return \CustomerManagementFramework\RESTApi\SegmentsHandler
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiSegmentsHandler();
    }
}
