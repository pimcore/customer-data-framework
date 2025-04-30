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

namespace CustomerManagementFrameworkBundle\RESTApi;

use CustomerManagementFrameworkBundle\RESTApi\Exception\MissingRequestBodyException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Routing handler implementation using the symfony route component to dispatch requests to actions.
 *
 * @package CustomerManagementFramework\RESTApi
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @param mixed $listing
     * @param int $defaultPageSize
     * @param int $defaultPage
     *
     */
    protected function handlePaginatorParams(
        $listing,
        Request $request,
        $defaultPageSize = 100,
        $defaultPage = 1
    ): PaginationInterface {
        $pageSize = $request->query->getInt('pageSize', $defaultPageSize);
        $page = $request->query->getInt('page', $defaultPage);

        return $this->paginator->paginate($listing, $page, $pageSize);
    }

    /**
     * Parse request body JSON
     *
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
