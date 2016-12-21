<?php

namespace CustomerManagementFramework\Mailchimp\ExportToolkit\AttributeClusterInterpreter;

use CustomerManagementFramework\Factory;
use ExportToolkit\ExportService\AttributeClusterInterpreter\AbstractAttributeClusterInterpreter;

abstract class AbstractMailchimpInterpreter extends AbstractAttributeClusterInterpreter
{
    /**
     * @return \CustomerManagementFramework\Mailchimp\ExportService
     */
    public function getExportService()
    {
        return Factory::getInstance()->getMailchimpExportService();
    }
}
