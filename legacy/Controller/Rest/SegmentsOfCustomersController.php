<?php
namespace CustomerManagementFrameworkBundle\Controller\Rest;

use CustomerManagementFrameworkBundle\Factory;

class SegmentsOfCustomersController extends RestHandlerController
{
    /**
     * @return \CustomerManagementFrameworkBundle\RESTApi\SegmentsOfCustomerHandler
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiSegmentsOfCustomerHandler();
    }
}
