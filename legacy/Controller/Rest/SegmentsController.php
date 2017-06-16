<?php

namespace CustomerManagementFrameworkBundle\Controller\Rest;

use CustomerManagementFrameworkBundle\Factory;

class SegmentsController extends RestHandlerController
{
    /**
     * @return \CustomerManagementFrameworkBundle\RESTApi\SegmentsHandler
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiSegmentsHandler();
    }
}
