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

namespace CustomerManagementFrameworkBundle\RESTApi;

use CustomerManagementFrameworkBundle\RESTApi\Exception\ResourceNotFoundException;
use CustomerManagementFrameworkBundle\RESTApi\Traits\ResourceUrlGenerator;
use CustomerManagementFrameworkBundle\RESTApi\Traits\ResponseGenerator;
use CustomerManagementFrameworkBundle\Service\ObjectToArray;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Pimcore\Model\DataObject\CustomerSegmentGroup;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\HttpFoundation\Request;

class SegmentGroupsHandler extends AbstractHandler implements CrudHandlerInterface
{
    use LoggerAware;
    use ResponseGenerator;
    use ResourceUrlGenerator;

    /**
     * GET /segment-groups
     *
     *
     * @return Response
     */
    public function listRecords(Request $request)
    {
        $list = new CustomerSegmentGroup\Listing();

        $list->setOrderKey('id');
        $list->setOrder('asc');
        $list->setUnpublished(false);

        $paginator = $this->handlePaginatorParams($list, $request);

        $timestamp = time();

        $result = [];
        foreach ($paginator as $segment) {
            $result[] = $this->hydrateSegmentGroup($segment);
        }

        return new Response(
            [
                'page' => $paginator->getCurrentPageNumber(),
                'totalPages' => $paginator instanceof SlidingPaginationInterface ? $paginator->getPaginationData()['pageCount'] : 0,
                'timestamp' => $timestamp,
                'data' => $result,
            ]
        );
    }

    /**
     * GET /segments/{id}
     *
     *
     * @return Response
     */
    public function readRecord(Request $request)
    {
        $segmentGroup = $this->loadSegmentGroup($request->attributes->getInt('id'));

        return $this->createSegmentGroupResponse($segmentGroup);
    }

    /**
     * POST /segments
     *
     *
     * @return Response
     */
    public function createRecord(Request $request)
    {
        $data = $this->getRequestData($request);

        if (empty($data['name'])) {
            return new Response(
                [
                    'success' => false,
                    'msg' => 'name required',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($data['reference'] && \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentGroupByReference(
            $data['reference'],
            (bool)$data['calculated']
        )
        ) {
            return new Response(
                [
                    'success' => false,
                    'msg' => sprintf(
                        "duplicate segment group - group with reference '%s' already exists",
                        $data['reference']
                    ),
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $segmentGroup = \Pimcore::getContainer()->get('cmf.segment_manager')->createSegmentGroup(
            $data['name'],
            $data['reference'],
            isset($data['calculated']) ? (bool)$data['calculated'] : false,
            $data
        );

        $result = ObjectToArray::getInstance()->toArray($segmentGroup);
        $result['success'] = true;

        return new Response($result);
    }

    /**
     * PUT /segments/{id}
     *
     * TODO support partial updates as we do now or demand whole object in PUT? Use PATCH for partial requests?
     *
     *
     * @return Response
     */
    public function updateRecord(Request $request)
    {
        $data = $this->getRequestData($request);

        if (!$request->attributes->has('id')) {
            return new Response(
                [
                    'success' => false,
                    'msg' => 'id required',
                ],
                Response::RESPONSE_CODE_BAD_REQUEST
            );
        }

        $id = $request->attributes->getInt('id');

        if (!$segmentGroup = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentGroupById(
            $id
        )
        ) {
            return new Response(
                [
                    'success' => false,
                    'msg' => sprintf('segment with id %s not found', $id),
                ],
                Response::RESPONSE_CODE_NOT_FOUND
            );
        }

        \Pimcore::getContainer()->get('cmf.segment_manager')->updateSegmentGroup($segmentGroup, $data);

        $result = $this->hydrateSegmentGroup($segmentGroup);
        $result['success'] = true;

        return new Response($result, Response::RESPONSE_CODE_OK);
    }

    /**
     * DELETE /segments/{id}
     *
     *
     * @return Response
     */
    public function deleteRecord(Request $request)
    {
        $segmentGroup = $this->loadSegmentGroup($request->attributes->getInt('id'));

        try {
            $segmentGroup->delete();
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }

        return $this->createResponse(null, Response::RESPONSE_CODE_NO_CONTENT);
    }

    /**
     * Load a customer segment group from ID.
     *
     * @param int|array $id
     *
     * @return CustomerSegmentGroup
     */
    protected function loadSegmentGroup($id)
    {
        if (is_array($id)) {
            if (!isset($id['id'])) {
                // this should never happen as the route demands an ID in the request
                throw new ResourceNotFoundException('Record ID is missing');
            }

            $id = $id['id'];
        }

        if ($id) {
            $id = (int)$id;
        }

        $segment = CustomerSegmentGroup::getById($id);
        if (!$segment) {
            throw new ResourceNotFoundException(sprintf('Segment group with ID %d was not found', $id));
        }

        return $segment;
    }

    /**
     * Create customer segment response with hydrated segment data
     *
     *
     * @return Response
     */
    protected function createSegmentGroupResponse(CustomerSegmentGroup $segmentGroup)
    {
        $response = $this->createResponse(
            $this->hydrateSegmentGroup($segmentGroup)
        );

        return $response;
    }

    /**
     *
     * @return array
     */
    protected function hydrateSegmentGroup(CustomerSegmentGroup $customerSegmentGroup)
    {
        $data = ObjectToArray::getInstance()->toArray($customerSegmentGroup);

        $links = $data['_links'] ?? [];

        if ($selfLink = $this->generateResourceApiUrl($customerSegmentGroup->getId())) {
            $links[] = [
                'rel' => 'self',
                'href' => $selfLink,
                'method' => 'GET',
            ];
        }

        $data['_links'] = $links;

        return $data;
    }
}
