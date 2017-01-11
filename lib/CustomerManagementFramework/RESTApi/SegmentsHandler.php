<?php

namespace CustomerManagementFramework\RESTApi;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use CustomerManagementFramework\RESTApi\Exception\ResourceNotFoundException;
use CustomerManagementFramework\RESTApi\Traits\ResourceUrlGenerator;
use CustomerManagementFramework\RESTApi\Traits\ResponseGenerator;
use CustomerManagementFramework\Traits\LoggerAware;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\CustomerSegmentGroup;

class SegmentsHandler extends AbstractCrudRoutingHandler
{
    use LoggerAware;
    use ResponseGenerator;
    use ResourceUrlGenerator;


    /**
     * GET /segments
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function listRecords(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $list = new CustomerSegment\Listing();

        $list->setOrderKey('o_id');
        $list->setOrder('asc');
        $list->setUnpublished(false);

        $paginator = new \Zend_Paginator($list);
        $this->handlePaginatorParams($paginator, $request);

        $timestamp = time();

        $result = [];
        foreach ($paginator as $segment) {
            $result[] = $this->hydrateSegment($segment);
        }

        return new Response([
            'page'       => $paginator->getCurrentPageNumber(),
            'totalPages' => $paginator->getPages()->pageCount,
            'timestamp'  => $timestamp,
            'data'       => $result
        ]);
    }

    /**
     * GET /segments/{id}
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function readRecord(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $segment = $this->loadSegment($params);

        return $this->createSegmentResponse($segment);
    }

    /**
     * POST /segments
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function createRecord(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $data = $this->getRequestData($request);

        if(!$data['group']) {
            return new Response([
                'success' => false,
                'msg' => "group required"
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }
        if(!$segmentGroup = CustomerSegmentGroup::getById($data['group'])) {
            return new Response([
                'success' => false,
                'msg' => "group not found"
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        if(!$data['name']) {
            return new Response([
                'success' => false,
                'msg' => "name required"
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        if($params['reference'] && Factory::getInstance()->getSegmentManager()->getSegmentByReference($data['reference'], $segmentGroup)) {
            return new Response([
                'success' => false,
                'msg' => sprintf("duplicate segment - segment with reference '%s' already exists in this group", $data['reference'])
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        $params['calculated'] = isset($data['calculated']) ? $data['calculated'] : $segmentGroup->getCalculated();

        $segment = Factory::getInstance()->getSegmentManager()->createSegment($data['name'], $segmentGroup, $data['reference'], (bool)$data['calculated'], $data['subFolder']);

        $result = $this->hydrateSegment($segment);
        $result['success'] = true;

        return new Response($result);
    }

    /**
     * PUT /segments/{id}
     *
     * TODO support partial updates as we do now or demand whole object in PUT? Use PATCH for partial requests?
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function updateRecord(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $data = $this->getRequestData($request);

        if(empty($params['id'])) {
            return new Response([
                'success' => false,
                'msg' => 'id required'
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        if(!$segment = CustomerSegment::getByid($params['id'])) {
            return new Response([
                'success' => false,
                'msg' => sprintf('segment with id %s not found', $params['id'])
            ], Response::RESPONSE_CODE_NOT_FOUND);
        }

        Factory::getInstance()->getSegmentManager()->updateSegment($segment, $data);

        $result = $this->hydrateSegment($segment);
        $result['success'] = true;

        return new Response($result, Response::RESPONSE_CODE_OK);
    }

    /**
     * DELETE /segments/{id}
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function deleteRecord(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $segment = $this->loadSegment($params);

        try {
            $segment->delete();
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }

        return $this->createResponse(null, Response::RESPONSE_CODE_NO_CONTENT);
    }

    /**
     * Load a customer segment from ID.
     *
     * @param int|array $id
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

        $segment = CustomerSegment::getById($id);
        if (!$segment) {
            throw new ResourceNotFoundException(sprintf('Segment with ID %d was not found', $id));
        }

        return $segment;
    }

    /**
     * @param \Zend_Paginator $paginator
     * @param \Zend_Controller_Request_Http $request
     * @param int $defaultPageSize
     * @param int $defaultPage
     */
    protected function handlePaginatorParams(\Zend_Paginator $paginator, \Zend_Controller_Request_Http $request, $defaultPageSize = 100, $defaultPage = 1)
    {
        $pageSize = intval($request->getParam('pageSize', $defaultPageSize));
        $page     = intval($request->getParam('page', $defaultPage));

        $paginator->setItemCountPerPage($pageSize);
        $paginator->setCurrentPageNumber($page);
    }

    /**
     * Create customer segment response with hydrated segment data
     *
     * @param CustomerSegmentInterface $segment
     *
     * @return Response
     * @internal param \Zend_Controller_Request_Http $request
     * @internal param ExportCustomersFilterParams $params
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
     * @return array
     */
    protected function hydrateSegment(CustomerSegmentInterface $customerSegment)
    {
        $data = $customerSegment->getDataForWebserviceExport();

        $links = isset($data['_links']) ? $data['_links'] : [];

        if ($selfLink = $this->generateElementApiUrl($customerSegment)) {
            $links[] = [
                'rel'    => 'self',
                'href'   => $selfLink,
                'method' => 'GET'
            ];
        }

        $data['_links'] = $links;

        return $data;
    }
}
