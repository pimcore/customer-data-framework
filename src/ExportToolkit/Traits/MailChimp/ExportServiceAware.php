<?php

namespace CustomerManagementFrameworkBundle\ExportToolkit\Traits\MailChimp;

use CustomerManagementFrameworkBundle\ExportToolkit\ExportService\MailChimpExportService;

// TODO update ExportToolkit to allow resolving objects via DI, so we can just inject the services we need
trait ExportServiceAware
{
    /**
     * @return \CustomerManagementFrameworkBundle\ExportToolkit\ExportService\MailChimpExportService
     */
    public function getExportService()
    {
        return \Pimcore::getContainer()->get('cmf.mailchimp.export_service');
    }
}
