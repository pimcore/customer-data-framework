<?php

namespace CustomerManagementFramework\RESTApi;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use CustomerManagementFramework\RESTApi\Exception\ResourceNotFoundException;
use CustomerManagementFramework\RESTApi\Traits\ResourceUrlGenerator;
use CustomerManagementFramework\RESTApi\Traits\ResponseGenerator;
use CustomerManagementFramework\Service\ObjectToArray;
use CustomerManagementFramework\Traits\LoggerAware;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\CustomerSegmentGroup;

class SegmentGroupsHandler extends AbstractCrudRoutingHandler
{
    use LoggerAware;
    use ResponseGenerator;
    use ResourceUrlGenerator;


    /**
     * GET /segment-groups
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function listRecords(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $list = new CustomerSegmentGroup\Listing();

        $list->setOrderKey('o_id');
        $list->setOrder('asc');
        $list->setUnpublished(false);

        $paginator = new \Zend_Paginator($list);
        $this->handlePaginatorParams($paginator, $request);

        $timestamp = time();

        $result = [];
        foreach ($paginator as $segment) {
            $result[] = $this->hydrateSegmentGroup($segment);
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
        $segment = $this->loadSegmentGroup($params);

        return $this->createSegmentGroupResponse($segment);
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

        if(empty($data['name'])) {
            return new Response([
                'success' => false,
                'msg' => 'name required'
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        if($data['reference'] && Factory::getInstance()->getSegmentManager()->getSegmentGroupByReference($data['reference'], (bool)$data['calculated'])) {
            return new Response([
                'success' => false,
                'msg' => sprintf("duplicate segment group - group with reference '%s' already exists", $data['reference'])
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        $segmentGroup = Factory::getInstance()->getSegmentManager()->createSegmentGroup($data['name'], $data['reference'], isset($data['calculated']) ? (bool)$data['calculated'] : false, $data);


        $result = ObjectToArray::getInstance()->toArray($segmentGroup);
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

        if(!$segmentGroup = CustomerSegmentGroup::getByid($params['id'])) {
            return new Response([
                'success' => false,
                'msg' => sprintf('segment with id %s not found', $params['id'])
            ], Response::RESPONSE_CODE_NOT_FOUND);
        }

        Factory::getInstance()->getSegmentManager()->updateSegmentGroup($segmentGroup, $data);

        $result = $this->hydrateSegmentGroup($segmentGroup);
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
        $segmentGroup = $this->loadSegmentGroup($params);

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
     * @return CustomerSegmentInterface|Concrete
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
     * @param CustomerSegmentGroup $segmentGroup
     *
     * @return Response
     * @internal param \Zend_Controller_Request_Http $request
     * @internal param ExportCustomersFilterParams $params
     */
    protected function createSegmentGroupResponse(CustomerSegmentGroup $segmentGroup)
    {

        $response = $this->createResponse(
            $this->hydrateSegmentGroup($segmentGroup)
        );

        return $response;
    }

    /**
     * @param CustomerSegmentGroup $customerSegmentGroup
     * @return array
     */
    protected function hydrateSegmentGroup(CustomerSegmentGroup $customerSegmentGroup)
    {
        $data = ObjectToArray::getInstance()->toArray($customerSegmentGroup);

        $links = isset($data['_links']) ? $data['_links'] : [];

        if ($selfLink = $this->generateElementApiUrl($customerSegmentGroup)) {
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
