<?php

namespace CustomerManagementFrameworkBundle\Controller\Rest;

use CustomerManagementFrameworkBundle\RESTApi\CrudHandlerInterface;
use CustomerManagementFrameworkBundle\RESTApi\Exception\ExceptionInterface;
use CustomerManagementFrameworkBundle\RESTApi\Exception\ResourceNotFoundException;
use CustomerManagementFrameworkBundle\RESTApi\HandlerInterface;
use CustomerManagementFrameworkBundle\RESTApi\Response;
use CustomerManagementFrameworkBundle\RESTApi\Traits\ResponseGenerator;
use Pimcore\Bundle\AdminBundle\Controller\Rest\AbstractRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


abstract class CrudHandlerController extends RestHandlerController
{

    /**
     * @return CrudHandlerInterface
     */
    abstract protected function getHandler();

    /**
     * @param Request $request
     * @Route("")
     * @Method({"GET"})
     */
    public function listRecords(Request $request)
    {
        $handler = $this->getHandler();
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
     * @param Request $request
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function readRecord(Request $request)
    {
        $handler = $this->getHandler();
        $response = null;

        try {
            $response = $handler->readRecord($request);
        } catch (ExceptionInterface $e) {

            $response = $this->createErrorResponse(
                $e->getMessage(),
                $e->getResponseCode() > 0 ? $e->getResponseCode() : 400
            );
        }

        return $response;
    }

    /**
     * @param Request $request
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function deleteRecord(Request $request)
    {
        $handler = $this->getHandler();
        $response = null;

        try {
            $response = $handler->deleteRecord($request);
        } catch (ExceptionInterface $e) {

            $response = $this->createErrorResponse(
                $e->getMessage(),
                $e->getResponseCode() > 0 ? $e->getResponseCode() : 400
            );
        }

        return $response;
    }

    /**
     * @param Request $request
     * @Route("/{id}")
     * @Method({"PUT", "POST"})
     */
    public function updateRecord(Request $request)
    {
        $handler = $this->getHandler();
        $response = null;

        try {
            $response = $handler->updateRecord($request);
        } catch (ExceptionInterface $e) {
            $response = $this->createErrorResponse(
                $e->getMessage(),
                $e->getResponseCode() > 0 ? $e->getResponseCode() : 400
            );
        }

        return $response;
    }

    /**
     * @param Request $request
     * @Route("")
     * @Method({"PUT", "POST"})
     */
    public function createRecord(Request $request)
    {
        $handler = $this->getHandler();
        $response = null;

        try {
            $response = $handler->createRecord($request);
        } catch (ExceptionInterface $e) {

            $response = $this->createErrorResponse(
                $e->getMessage(),
                $e->getResponseCode() > 0 ? $e->getResponseCode() : 400
            );
        }

        return $response;
    }
}
