<?php

namespace CustomerManagementFrameworkBundle\Controller\Rest\Api;

use CustomerManagementFrameworkBundle\Controller\Rest\CrudHandlerController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/segment-groups")
 */
class SegmentGroupsController extends CrudHandlerController
{
    /**
     * @return \CustomerManagementFrameworkBundle\RESTApi\SegmentGroupsHandler
     */
    protected function getHandler()
    {
        return \Pimcore::getContainer()->get('cmf.rest.segment_groups_handler');
    }
}
