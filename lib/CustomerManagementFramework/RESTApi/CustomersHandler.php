<?php

namespace CustomerManagementFramework\RESTApi;

use CustomerManagementFramework\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFramework\Filter\ExportCustomersFilterParams;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\RESTApi\Exception\ResourceNotFoundException;
use CustomerManagementFramework\RESTApi\Traits\ResourceUrlGenerator;
use CustomerManagementFramework\Traits\LoggerAware;
use Pimcore\Model\Object\Concrete;
use Symfony\Component\Routing\RouteCollection;

class CustomersHandler extends AbstractRoutingHandler
{
    use LoggerAware;
    use ResourceUrlGenerator;

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @var ExportInterface
     */
    protected $export;

    /**
     * @param CustomerProviderInterface $customerProvider
     * @param ExportInterface $export
     */
    public function __construct(CustomerProviderInterface $customerProvider, ExportInterface $export)
    {
        $this->customerProvider = $customerProvider;
        $this->export           = $export;
    }

    /**
     * @inheritDoc
     */
    protected function getRoutes()
    {
        $routes = new RouteCollection();

        $routes->add(
            'list',
            $this->createRoute('GET', '/', 'listRecords')
        );

        $routes->add(
            'read',
            $this
                ->createRoute('GET', '/{id}', 'readRecord')
                ->setRequirement('id', '\d+')
        );

        $routes->add(
            'create',
            $this->createRoute('POST', '/', 'createRecord')
        );

        $routes->add(
            'update',
            $this
                ->createRoute('PUT', '/{id}', 'updateRecord')
                ->setRequirement('id', '\d+')
        );

        $routes->add(
            'delete',
            $this
                ->createRoute('DELETE', '/{id}', 'deleteRecord')
                ->setRequirement('id', '\d+')
        );

        return $routes;
    }

    /**
     * GET /customers
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function listRecords(\Zend_Controller_Request_Http $request, array $params = [])
    {
        return $this->export->exportAction('customers', $request);
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

        /** @var CustomerInterface|Concrete $customer */
        $customer = $this->customerProvider->create($data);
        $customer->save();

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

        $this->customerProvider->update($customer, $data);
        $customer->save();

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

        $this->customerProvider->delete($customer);

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
     * Create a JSON response with normalized body containing timestamp
     *
     * TODO timestamp needed?
     * TODO use a standard format like JSON-API?
     *
     * @param array|null $data
     * @param $code
     * @return Response
     */
    protected function createResponse(array $data = null, $code = Response::RESPONSE_CODE_OK)
    {
        $responseData = null;
        if (null !== $data) {
            $responseData = [
                'timestamp' => time()
            ];

            $responseData['data'] = $data;
        }

        return new Response($responseData, $code);
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
            $this->export->hydrateCustomer($customer, $params)
        );

        $this->addCustomerResponseLinks($customer, $response);

        return $response;
    }

    /**
     * @param CustomerInterface $customer
     * @param Response $response
     * @return Response
     */
    protected function addCustomerResponseLinks(CustomerInterface $customer, Response $response)
    {
        if (!is_array($response->getData())) {
            return $response;
        }

        $data  = $response->getData();
        $links = isset($data['links']) ? $data['links'] : [];

        if ($selfLink = $this->generateElementApiUrl($customer)) {
            $links[] = [
                'rel'    => 'self',
                'href'   => $selfLink,
                'method' => 'GET'
            ];
        }

        if (!empty($links)) {
            $data['links'] = $links;
            $response->setData($data);
        }

        return $response;
    }
}
