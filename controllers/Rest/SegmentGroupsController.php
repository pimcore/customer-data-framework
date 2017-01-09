<?php

use CustomerManagementFramework\Controller\Rest\RestHandlerController;
use CustomerManagementFramework\Factory;

class CustomerManagementFramework_Rest_SegmentGroupsController extends RestHandlerController
{
    /**
     * @return \CustomerManagementFramework\RESTApi\SegmentGroupsHandler
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiSegmentGroupsHandler();
    }
}
