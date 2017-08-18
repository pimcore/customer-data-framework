<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Controller\Rest;

use CustomerManagementFrameworkBundle\RESTApi\CrudHandlerInterface;
use CustomerManagementFrameworkBundle\RESTApi\Exception\ExceptionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
