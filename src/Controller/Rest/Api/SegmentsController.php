<?php

namespace CustomerManagementFrameworkBundle\Controller\Rest\Api;

use CustomerManagementFrameworkBundle\Controller\Rest\CrudHandlerController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/segments")
 */
class SegmentsController extends CrudHandlerController
{
    /**
     * @return \CustomerManagementFrameworkBundle\RESTApi\SegmentsHandler
     */
    protected function getHandler()
    {
        return \Pimcore::getContainer()->get('cmf.rest.segments_handler');
    }
}
