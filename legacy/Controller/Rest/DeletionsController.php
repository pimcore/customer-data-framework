<?php

namespace CustomerManagementFrameworkBundle\Controller\Rest;

use CustomerManagementFrameworkBundle\Factory;

class DeletionsController extends RestHandlerController
{
    /**
     * @return \CustomerManagementFrameworkBundle\RESTApi\DeletionsHandler
     */
    protected function getHandler()
    {
        return Factory::getInstance()->getRESTApiDeletionsHandler();
    }
}
