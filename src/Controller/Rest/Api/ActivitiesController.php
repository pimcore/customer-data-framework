<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Controller\Rest\Api;

use CustomerManagementFrameworkBundle\Controller\Rest\CrudHandlerController;
use CustomerManagementFrameworkBundle\RESTApi\ActivitiesHandler;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/activities')]
class ActivitiesController extends CrudHandlerController
{
    protected function getHandler(): ActivitiesHandler
    {
        return \Pimcore::getContainer()->get('cmf.rest.activities_handler');
    }
}
