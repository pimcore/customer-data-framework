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

use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\RESTApi\Exception\ResourceNotFoundException;
use CustomerManagementFrameworkBundle\RESTApi\Traits\ResourceUrlGenerator;
use CustomerManagementFrameworkBundle\RESTApi\Traits\ResponseGenerator;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\CustomerSegment;
use Pimcore\Model\DataObject\CustomerSegmentGroup;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\HttpFoundation\Request;

class SegmentsHandler extends AbstractHandler implements CrudHandlerInterface
{
    use LoggerAware;
    use ResponseGenerator;
    use ResourceUrlGenerator;

    /**
     * GET /segments
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listRecords(Request $request)
    {
        $list = new CustomerSegment\Listing();

        $list->setOrderKey(Service::getVersionDependentDatabaseColumnName('id'));
        $list->setOrder('asc');
        $list->setUnpublished(false);

        $paginator = $this->handlePaginatorParams($list, $request);

        $timestamp = time();

        $result = [];
        foreach ($paginator as $segment) {
            $result[] = $this->hydrateSegment($segment);
        }

        return new Response(
            [
                'page' => $paginator->getCurrentPageNumber(),
                'totalPages' => $paginator->getPaginationData()['pageCount'],
                'timestamp' => $timestamp,
                'data' => $result,
            ]
        );
    }

    /**
     * GET /segments/{id}
     *
     * @param Request $request
     *
     * @return Response
     */
    public function readRecord(Request $request)
    {
        $segment = $this->loadSegment($request->get('id'));

        return $this->createSegmentResponse($segment);
    }

    /**
     * POST /segments
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createRecord(Request $request)
    {
        $data = $this->getRequestData($request);

        if (!$data['group']) {
            return new Response(
                [
                    'success' => false,
                    'msg' => 'group required',
                ],
                Response::RESPONSE_CODE_BAD_REQUEST
            );
        }
        if (!$segmentGroup = CustomerSegmentGroup::getById($data['group'])) {
            return new Response(
                [
                    'success' => false,
                    'msg' => 'group not found',
                ],
                Response::RESPONSE_CODE_BAD_REQUEST
            );
        }

        if (!$data['name']) {
            return new Response(
                [
                    'success' => false,
                    'msg' => 'name required',
                ],
                Response::RESPONSE_CODE_BAD_REQUEST
            );
        }

        if ($data['reference'] && \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentByReference(
                $data['reference'],
                $segmentGroup
            )
        ) {
            return new Response(
                [
                    'success' => false,
                    'msg' => sprintf(
                        "duplicate segment - segment with reference '%s' already exists in this group",
                        $data['reference']
                    ),
                ],
                Response::RESPONSE_CODE_BAD_REQUEST
            );
        }

        $data['calculated'] = isset($data['calculated']) ? $data['calculated'] : $segmentGroup->getCalculated();

        $segment = \Pimcore::getContainer()->get('cmf.segment_manager')->createSegment(
            $data['name'],
            $segmentGroup,
            $data['reference'],
            (bool)$data['calculated'],
            $data['subFolder']
        );

        $result = $this->hydrateSegment($segment);
        $result['success'] = true;

        return new Response($result);
    }

    /**
     * PUT /segments/{id}
     *
     * TODO support partial updates as we do now or demand whole object in PUT? Use PATCH for partial requests?
     *
     * @param Request $request
     *
     * @return Response
     */
    public function updateRecord(Request $request)
    {
        $data = $this->getRequestData($request);

        if (empty($request->get('id'))) {
            return new Response(
                [
                    'success' => false,
                    'msg' => 'id required',
                ],
                Response::RESPONSE_CODE_BAD_REQUEST
            );
        }

        if (!$segment = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentByid($request->get('id'))) {
            return new Response(
                [
                    'success' => false,
                    'msg' => sprintf('segment with id %s not found', $request->get('id')),
                ],
                Response::RESPONSE_CODE_NOT_FOUND
            );
        }

        \Pimcore::getContainer()->get('cmf.segment_manager')->updateSegment($segment, $data);

        $result = $this->hydrateSegment($segment);
        $result['success'] = true;

        return new Response($result, Response::HTTP_OK);
    }

    /**
     * DELETE /segments/{id}
     *
     * @param Request $request
     *
     * @return Response
     */
    public function deleteRecord(Request $request)
    {
        $segment = $this->loadSegment($request->get('id'));

        try {
            $segment->delete();
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }

        return $this->createResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Load a customer segment from ID.
     *
     * @param int|array $id
     *
     * @return CustomerSegmentInterface|Concrete
     */
    protected function loadSegment($id)
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

        $segment = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentByid($id);
        if (!$segment) {
            throw new ResourceNotFoundException(sprintf('Segment with ID %d was not found', $id));
        }

        return $segment;
    }

    /**
     * Create customer segment response with hydrated segment data
     *
     * @param CustomerSegmentInterface $segment
     *
     * @return Response
     */
    protected function createSegmentResponse(CustomerSegmentInterface $segment)
    {
        $response = $this->createResponse(
            $this->hydrateSegment($segment)
        );

        return $response;
    }

    /**
     * @param CustomerSegmentInterface $customerSegment
     *
     * @return array
     */
    protected function hydrateSegment(CustomerSegmentInterface $customerSegment)
    {
        $data = $customerSegment->getDataForWebserviceExport();

        $links = $data['_links'] ?? [];

        if ($selfLink = $this->generateResourceApiUrl($customerSegment->getId())) {
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
