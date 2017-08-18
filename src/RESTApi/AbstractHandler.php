<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\RESTApi;

use CustomerManagementFrameworkBundle\RESTApi\Exception\MissingRequestBodyException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Zend\Paginator\Paginator;

/**
 * Routing handler implementation using the symfony route component to dispatch requests to actions.
 *
 * @package CustomerManagementFramework\RESTApi
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @param Paginator $paginator
     * @param Request $request
     * @param int $defaultPageSize
     * @param int $defaultPage
     */
    protected function handlePaginatorParams(
        Paginator $paginator,
        Request $request,
        $defaultPageSize = 100,
        $defaultPage = 1
    ) {
        $pageSize = intval($request->get('pageSize', $defaultPageSize));
        $page = intval($request->get('page', $defaultPage));

        $paginator->setItemCountPerPage($pageSize);
        $paginator->setCurrentPageNumber($page);
    }

    /**
     * Parse request body JSON
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getRequestData(Request $request)
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (null === $data) {
            throw new MissingRequestBodyException(
                'Request body is no valid JSON',
                Response::HTTP_BAD_REQUEST
            );
        }

        return $data;
    }
}
