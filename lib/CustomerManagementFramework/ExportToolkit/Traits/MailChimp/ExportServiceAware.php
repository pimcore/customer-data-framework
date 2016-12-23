<?php

namespace CustomerManagementFramework\ExportToolkit\Traits\MailChimp;

use CustomerManagementFramework\ExportToolkit\ExportService\MailChimpExportService;

// TODO update ExportToolkit to allow resolving objects via DI, so we can just inject the services we need
trait ExportServiceAware
{
    /**
     * @return \CustomerManagementFramework\ExportToolkit\ExportService\MailChimpExportService
     */
    public function getExportService()
    {
        return \Pimcore::getDiContainer()->get(MailChimpExportService::class);
    }
}
