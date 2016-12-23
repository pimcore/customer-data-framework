<?php

namespace CustomerManagementFramework\ExportToolkit\AttributeClusterInterpreter\MailChimp;

use CustomerManagementFramework\ExportToolkit\ExportService\MailChimpExportService;
use ExportToolkit\ExportService\AttributeClusterInterpreter\AbstractAttributeClusterInterpreter;

abstract class AbstractMailChimpInterpreter extends AbstractAttributeClusterInterpreter
{
    /**
     * @return \CustomerManagementFramework\ExportToolkit\ExportService\MailChimpExportService
     */
    public function getExportService()
    {
        return \Pimcore::getDiContainer()->get(MailChimpExportService::class);
    }
}
