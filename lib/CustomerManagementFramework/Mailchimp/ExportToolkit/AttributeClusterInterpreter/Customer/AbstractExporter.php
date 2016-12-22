<?php

namespace CustomerManagementFramework\Mailchimp\ExportToolkit\AttributeClusterInterpreter\Customer;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Mailchimp\ExportService;
use CustomerManagementFramework\Mailchimp\ExportToolkit\AttributeClusterInterpreter\Customer;
use CustomerManagementFramework\Model\CustomerInterface;
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
     * @var ExportService
     */
    protected $exportService;

    /**
     * @var MailChimp
     */
    protected $apiClient;

    /**
     * AbstractExporter constructor.
     * @param Customer $interpreter
     */
    public function __construct(Customer $interpreter)
    {
        $this->interpreter = $interpreter;
        $this->logger      = $interpreter->getLogger();

        $this->setExportService($interpreter->getExportService());
    }

    /**
     * @param ExportService $exportService
     * @return $this
     */
    public function setExportService(ExportService $exportService)
    {
        $this->exportService = $exportService;
        $this->apiClient     = $exportService->getApiClient();

        return $this;
    }

    /**
     * @param int $id
     * @return CustomerInterface|ElementInterface|null
     */
    protected function getCustomer($id)
    {
        return Factory::getInstance()
            ->getCustomerProvider()
            ->getById($id);
    }

    /**
     * Run the actual export
     */
    abstract public function export();
}
