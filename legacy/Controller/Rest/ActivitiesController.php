<?php

namespace CustomerManagementFrameworkBundle\Controller\Rest;

use CustomerManagementFrameworkBundle\Factory;

class ActivitiesController extends RestHandlerController
{
    /**
     * @return \CustomerManagementFrameworkBundle\RESTApi\ActivitiesHandler
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiActivitiesHandler();
    }
}
