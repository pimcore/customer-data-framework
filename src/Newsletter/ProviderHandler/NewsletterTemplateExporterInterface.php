<?php
/**
 * Created by PhpStorm.
 * User: tmittendorfer
 * Date: 13.07.2018
 * Time: 09:37
 */

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler;

use Pimcore\Model\Document;



interface NewsletterTemplateExporterInterface {

    public function exportTemplate(Document $document);
}