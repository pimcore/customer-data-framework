<?php

namespace CustomerManagementFrameworkBundle\Controller\Rest\Api;

use CustomerManagementFrameworkBundle\Controller\Rest\CrudHandlerController;
use CustomerManagementFrameworkBundle\RESTApi\CustomersHandler;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/customers")
 */
class CustomersController extends CrudHandlerController
{
    /**
     * @return CustomersHandler
     */
    protected function getHandler()
    {
        return \Pimcore::getContainer()->get('cmf.rest.customers_handler');
    }
}
