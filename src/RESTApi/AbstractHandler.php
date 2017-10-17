<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
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
