<?php

namespace CustomerManagementFramework\RESTApi;

use CustomerManagementFramework\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFramework\Filter\ExportCustomersFilterParams;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Traits\LoggerAware;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Object\Concrete;
use Psr\Log\LoggerInterface;

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
    }

    /**
     * @param ElementInterface|CustomerInterface $record
     * @return Response
     */
    public function readRecord(ElementInterface $record)
    {
        $params = ExportCustomersFilterParams::fromRequest($this->request);

        return new Response([
            'timestamp' => time(),
            'data'      => $this->export->hydrateCustomer($record, $params)
        ]);
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

        return new Response(null, Response::RESPONSE_CODE_NO_CONTENT);
    }
}
