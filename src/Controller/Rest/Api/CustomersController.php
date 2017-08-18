<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

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
