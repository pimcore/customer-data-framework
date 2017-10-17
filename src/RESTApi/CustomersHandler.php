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

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Filter\ExportCustomersFilterParams;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\RESTApi\Exception\ResourceNotFoundException;
use CustomerManagementFrameworkBundle\RESTApi\Traits\ResourceUrlGenerator;
use CustomerManagementFrameworkBundle\RESTApi\Traits\ResponseGenerator;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\HttpFoundation\Request;
use Zend\Paginator\Paginator;

class CustomersHandler extends AbstractHandler implements CrudHandlerInterface
{
    use LoggerAware;
    use ResponseGenerator;
    use ResourceUrlGenerator;

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @param CustomerProviderInterface $customerProvider
     */
    public function __construct(CustomerProviderInterface $customerProvider)
    {
        $this->customerProvider = $customerProvider;
    }

    /**
     * GET /customers
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listRecords(Request $request)
    {
        $params = ExportCustomersFilterParams::fromRequest($request);

        if ($params->getSegments()) {
            $customers = \Pimcore::getContainer()->get('cmf.segment_manager')->getCustomersBySegmentIds(
                $params->getSegments()
            );
        } else {
            $customers = \Pimcore::getContainer()->get('cmf.customer_provider')->getList();
        }

        $customers->setOrderKey('o_id');
        $customers->setOrder('asc');
        $customers->setUnpublished(false);

        $paginator = new Paginator($customers);
        $this->handlePaginatorParams($paginator, $request);

        $timestamp = time();

        $result = [];
        foreach ($paginator as $customer) {
            $result[] = $this->hydrateCustomer($customer, $params);
        }

        return new Response(
            [
                'page' => $paginator->getCurrentPageNumber(),
                'totalPages' => $paginator->getPages()->pageCount,
                'timestamp' => $timestamp,
                'data' => $result,
            ]
        );
    }

    /**
     * GET /customers/{id}
     *
     * @param Request $request
     *
     * @return Response
     */
    public function readRecord(Request $request)
    {
        $customer = $this->loadCustomer($request->get('id'));

        return $this->createCustomerResponse($customer, $request);
    }

    /**
     * POST /customers
     *
     * @param Request $request
     * @param array $params
     *
     * @return Response
     */
    public function createRecord(Request $request)
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
        $response->setStatusCode(Response::RESPONSE_CODE_CREATED);

        return $response;
    }

    /**
     * PUT /customers/{id}
     *
     * TODO support partial updates as we do now or demand whole object in PUT? Use PATCH for partial requests?
     *
     * @param Request $request
     *
     * @return Response
     */
    public function updateRecord(Request $request)
    {
        $customer = $this->loadCustomer($request->get('id'));
        $data = $this->getRequestData($request);

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
     * @param Request $request
     * @param array $params
     *
     * @return Response
     */
    public function deleteRecord(Request $request)
    {
        $customer = $this->loadCustomer($request->get('id'));

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
     *
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
     * Create customer response with hydrated customer data
     *
     * @param CustomerInterface $customer
     * @param Request $request
     * @param ExportCustomersFilterParams $params
     *
     * @return Response
     */
    protected function createCustomerResponse(
        CustomerInterface $customer,
        Request $request,
        ExportCustomersFilterParams $params = null
    ) {
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
     *
     * @return array
     */
    protected function hydrateCustomer(CustomerInterface $customer, ExportCustomersFilterParams $params)
    {
        $data = $customer->cmfToArray();

        if ($params->getIncludeActivities()) {
            $data['activities'] = \Pimcore::getContainer()->get('cmf.activity_store')->getActivityDataForCustomer(
                $customer
            );
        }

        $links = isset($data['_links']) ? $data['_links'] : [];

        if ($selfLink = $this->generateResourceApiUrl($customer->getId())) {
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
