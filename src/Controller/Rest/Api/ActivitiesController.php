<?php

namespace CustomerManagementFrameworkBundle\Controller\Rest\Api;

use CustomerManagementFrameworkBundle\Controller\Rest\CrudHandlerController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/activities")
 */
class ActivitiesController extends CrudHandlerController
{
    /**
     * @return \CustomerManagementFrameworkBundle\RESTApi\ActivitiesHandler
     */
    protected function getHandler()
    {
        return \Pimcore::getContainer()->get('cmf.rest.activities_handler');
    }
}
