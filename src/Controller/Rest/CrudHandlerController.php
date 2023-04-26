<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\Controller\Rest;

use CustomerManagementFrameworkBundle\RESTApi\CrudHandlerInterface;
use CustomerManagementFrameworkBundle\RESTApi\Exception\ExceptionInterface;
use CustomerManagementFrameworkBundle\RESTApi\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

abstract class CrudHandlerController extends RestHandlerController
{
    /**
     * @return CrudHandlerInterface
     */
    abstract protected function getHandler();

    /**
     * @Route("", methods={"GET"})
     */
    public function listRecords(Request $request): Response | JsonResponse
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
     * @Route("/{id}", methods={"GET"})
     */
    public function readRecord(Request $request): JsonResponse | Response
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
     * @Route("/{id}", methods={"DELETE"})
     */
    public function deleteRecord(Request $request): JsonResponse | Response
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
     * @Route("/{id}", methods={"PUT", "POST"})
     */
    public function updateRecord(Request $request): JsonResponse | Response
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
     * @Route("", methods={"PUT", "POST"})
     */
    public function createRecord(Request $request): JsonResponse | Response
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
