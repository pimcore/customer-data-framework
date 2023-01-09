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

namespace CustomerManagementFrameworkBundle\Controller\Rest\Api;

use CustomerManagementFrameworkBundle\Controller\Rest\RestHandlerController;
use CustomerManagementFrameworkBundle\RESTApi\DeletionsHandler;
use CustomerManagementFrameworkBundle\RESTApi\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/deletions")
 */
class DeletionsController extends RestHandlerController
{
    public function __construct(protected DeletionsHandler $handler)
    {
    }

    /**
     * @param Request $request
     * @Route("", methods={"GET"})
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

    protected function getHandler(): DeletionsHandler
    {
        return $this->handler;
    }
}
