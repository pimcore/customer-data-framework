<?php

use CustomerManagementFramework\Controller\Rest\RestHandlerController;
use CustomerManagementFramework\Factory;

class CustomerManagementFramework_Rest_SegmentsOfCustomersController extends RestHandlerController
{
    /**
     * @return \CustomerManagementFramework\RESTApi\SegmentsOfCustomerHandler
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiSegmentsOfCustomerHandler();
    }
}
