<?php

namespace CustomerManagementFramework\RESTApi;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Filter\ExportCustomersFilterParams;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\RESTApi\Exception\ResourceNotFoundException;
use CustomerManagementFramework\RESTApi\Traits\ResourceUrlGenerator;
use CustomerManagementFramework\RESTApi\Traits\ResponseGenerator;
use CustomerManagementFramework\Traits\LoggerAware;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\Object\CustomerSegment;

class SegmentsHandler extends AbstractCrudRoutingHandler
{
    use LoggerAware;
    use ResponseGenerator;
    use ResourceUrlGenerator;


    /**
     * GET /customers
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
        foreach ($paginator as $customer) {
            $result[] = $this->hydrateCustomer($customer, $params);
        }

        return new Response([
            'page'       => $paginator->getCurrentPageNumber(),
            'totalPages' => $paginator->getPages()->pageCount,
            'timestamp'  => $timestamp,
            'data'       => $result
        ]);
    }

    /**
     * GET /customers/{id}
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function readRecord(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $customer = $this->loadCustomer($params);

        return $this->createCustomerResponse($customer, $request);
    }

    /**
     * POST /customers
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function createRecord(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $data = $this->getRequestData($request);

        try {
            /** @var CustomerInterface|Concrete $customer */
            $customer = $this->customerProvider->create($data);
            $customer->save();
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }

        $response = $this->createCustomerResponse($customer, $request);
        $response->setResponseCode(Response::RESPONSE_CODE_CREATED);

        return $response;
    }

    /**
     * PUT /customers/{id}
     *
     * TODO support partial updates as we do now or demand whole object in PUT? Use PATCH for partial requests?
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function updateRecord(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $customer = $this->loadCustomer($params);
        $data     = $this->getRequestData($request);

        try {
            $this->customerProvider->update($customer, $data);
            $customer->save();
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }

        return $this->createCustomerResponse($customer, $request);
    }

    /**
     * DELETE /customers/{id}
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function deleteRecord(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $customer = $this->loadCustomer($params);

        try {
            $this->customerProvider->delete($customer);
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }

        return $this->createResponse(null, Response::RESPONSE_CODE_NO_CONTENT);
    }

    /**
     * Load a customer from ID/params array. If an array is passed, it tries to resolve the id from the 'id' property
     *
     * @param int|array $id
     * @return CustomerInterface|Concrete
     */
    protected function loadCustomer($id)
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

        $customer = $this->customerProvider->getById($id);
        if (!$customer) {
            throw new ResourceNotFoundException(sprintf('Customer with ID %d was not found', $id));
        }

        return $customer;
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
     * Create customer response with hydrated customer data
     *
     * @param CustomerInterface $customer
     * @param \Zend_Controller_Request_Http $request
     * @param ExportCustomersFilterParams $params
     * @return Response
     */
    protected function createCustomerResponse(CustomerInterface $customer, \Zend_Controller_Request_Http $request, ExportCustomersFilterParams $params = null)
    {
        if (null === $params) {
            $params = ExportCustomersFilterParams::fromRequest($request);
        }

        $response = $this->createResponse(
            $this->hydrateCustomer($customer, $params)
        );

        return $response;
    }

    /**
     * @param CustomerInterface $customer
     * @param ExportCustomersFilterParams $params
     * @return array
     */
    protected function hydrateCustomer(CustomerInterface $customer, ExportCustomersFilterParams $params)
    {
        $data = $customer->cmfToArray();

        if ($params->getIncludeActivities()) {
            $data['activities'] = Factory::getInstance()->getActivityStore()->getActivityDataForCustomer($customer);
        }

        $links = isset($data['_links']) ? $data['_links'] : [];

        if ($selfLink = $this->generateElementApiUrl($customer)) {
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
