<?php

namespace CustomerManagementFrameworkBundle\Controller\Rest;

use CustomerManagementFrameworkBundle\Factory;

class SegmentGroupsController extends RestHandlerController
{
    /**
     * @return \CustomerManagementFrameworkBundle\RESTApi\SegmentGroupsHandler
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiSegmentGroupsHandler();
    }
}
