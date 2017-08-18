<?php

namespace CustomerManagementFrameworkBundle\ExportToolkit\AttributeClusterInterpreter\MailChimp\Customer;

use CustomerManagementFrameworkBundle\ExportToolkit\AttributeClusterInterpreter\MailChimp\Customer;
use CustomerManagementFrameworkBundle\ExportToolkit\ExportService\MailChimpExportService;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use DrewM\MailChimp\MailChimp;
use Pimcore\Model\Element\ElementInterface;

abstract class AbstractExporter
{
    /**
     * @var Customer
     */
    protected $interpreter;

    /**
     * @var \Pimcore\Log\ApplicationLogger|\Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var MailChimpExportService
     */
    protected $exportService;

    /**
     * @var MailChimp
     */
    protected $apiClient;

    /**
     * AbstractExporter constructor.
     *
     * @param Customer $interpreter
     */
    public function __construct(Customer $interpreter)
    {
        $this->interpreter = $interpreter;
        $this->logger = $interpreter->getLogger();

        $this->setExportService($interpreter->getExportService());
    }

    /**
     * @param MailChimpExportService $exportService
     *
     * @return $this
     */
    public function setExportService(MailChimpExportService $exportService)
    {
        $this->exportService = $exportService;
        $this->apiClient = $exportService->getApiClient();

        return $this;
    }

    /**
     * @param int $id
     *
     * @return CustomerInterface|ElementInterface|null
     */
    protected function getCustomer($id)
    {
        return \Pimcore::getContainer()
            ->get('cmf.customer_provider')
            ->getById($id);
    }

    /**
     * Run the actual export
     */
    abstract public function export();
}
