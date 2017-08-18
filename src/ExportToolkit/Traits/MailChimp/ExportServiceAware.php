<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ExportToolkit\Traits\MailChimp;

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
