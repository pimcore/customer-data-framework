<?php

use CustomerManagementFramework\Controller\Rest\RestHandlerController;
use CustomerManagementFramework\Factory;

class CustomerManagementFramework_Rest_ActivitiesController extends RestHandlerController
{
    /**
     * @return \CustomerManagementFramework\RESTApi\ActivitiesHandler
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiActivitiesHandler();
    }
}
