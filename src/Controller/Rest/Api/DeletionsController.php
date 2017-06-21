<?php

namespace CustomerManagementFrameworkBundle\Controller\Rest\Api;

use CustomerManagementFrameworkBundle\Controller\Rest\RestHandlerController;
use CustomerManagementFrameworkBundle\RESTApi\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/deletions")
 */
class DeletionsController extends RestHandlerController
{
    /**
     * @param Request $request
     * @Route("")
     * @Method({"GET"})
     */
    public function listRecords(Request $request)
    {
        $handler  = $this->getHandler();
        $response = null;

        try {
            $response = $handler->listRecords($request);
        } catch (ExceptionInterface $e) {
            $response = $this->createErrorResponse(
                $e->getMessage(),
                $e->getResponseCode() > 0 ? $e->getResponseCode() : 400
            );
        }

        return $response;
    }

    /**
     * @return \CustomerManagementFrameworkBundle\RESTApi\DeletionsHandler
     */
    protected function getHandler()
    {
        return \Pimcore::getContainer()->get('cmf.rest.deletions_handler');
    }
}
