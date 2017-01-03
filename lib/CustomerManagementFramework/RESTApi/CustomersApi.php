<?php

namespace CustomerManagementFramework\RESTApi;

use CustomerManagementFramework\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFramework\Filter\ExportCustomersFilterParams;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Traits\LoggerAware;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Object\Concrete;
use Pimcore\View\Helper\Url;

class CustomersApi implements CrudInterface
{
    use LoggerAware;

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @var ExportInterface
     */
    protected $export;

    /**
     * @var string
     */
    protected $apiRoute;

    /**
     * @var string
     */
    protected $apiResourceRoute;

    /**
     * @var Url
     */
    protected $urlHelper;

    /**
     * @var \Zend_Controller_Request_Http
     */
    protected $request;

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
     * @param string $apiRoute
     * @return $this
     */
    public function setApiRoute($apiRoute)
    {
        $this->apiRoute = $apiRoute;

        return $this;
    }

    /**
     * @param string $apiResourceRoute
     * @return $this
     */
    public function setApiResourceRoute($apiResourceRoute)
    {
        $this->apiResourceRoute = $apiResourceRoute;

        return $this;
    }

    /**
     * @param Url $urlHelper
     * @return $this
     */
    public function setUrlHelper(Url $urlHelper)
    {
        $this->urlHelper = $urlHelper;

        return $this;
    }

    /**
     * @param \Zend_Controller_Request_Http $request
     * @return $this
     */
    public function setRequest(\Zend_Controller_Request_Http $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param mixed $id
     * @return ElementInterface|CustomerInterface
     */
    public function loadRecord($id)
    {
        return $this->customerProvider->getById($id);
    }

    /**
     * @return Response
     */
    public function listRecords()
    {
        return $this->export->exportAction('customers', $this->request);
    }

    /**
     * @param array $data
     * @return Response
     */
    public function createRecord(array $data)
    {
        /** @var CustomerInterface|Concrete $record */
        $record = $this->customerProvider->create($data);
        $record->save();

        $response = $this->readRecord($record);
        $response->setResponseCode(Response::RESPONSE_CODE_CREATED);

        // include location header
        if ($url = $this->generateRecordApiUrl($record)) {
            $response->setHeader('Location', $url);
        }

        return $response;
    }

    /**
     * @param ElementInterface|CustomerInterface $record
     * @return Response
     */
    public function readRecord(ElementInterface $record)
    {
        $params = ExportCustomersFilterParams::fromRequest($this->request);

        return $this->createResponse(
            $this->export->hydrateCustomer($record, $params),
            Response::RESPONSE_CODE_OK
        );
    }

    /**
     * @param ElementInterface|CustomerInterface|Concrete $record
     * @param array $data
     * @return Response
     */
    public function updateRecord(ElementInterface $record, array $data)
    {
        $this->customerProvider->update($record, $data);
        $record->save();

        return $this->readRecord($record);
    }

    /**
     * @param ElementInterface|CustomerInterface|Concrete $record
     * @return Response
     */
    public function deleteRecord(ElementInterface $record)
    {
        $this->customerProvider->delete($record);

        return $this->createResponse(null, Response::RESPONSE_CODE_NO_CONTENT);
    }

    /**
     * @param array|null $data
     * @param $code
     * @return Response
     */
    protected function createResponse(array $data = null, $code = Response::RESPONSE_CODE_OK)
    {
        // TODO add HATEOAS links here?
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
     * @param ElementInterface $record
     * @return null|string
     */
    protected function generateRecordApiUrl(ElementInterface $record)
    {
        if (!$this->urlHelper || !$this->apiResourceRoute) {
            return null;
        }

        return $this->urlHelper->url([
            'id' => $record->getId()
        ], $this->apiResourceRoute, true);
    }
}
