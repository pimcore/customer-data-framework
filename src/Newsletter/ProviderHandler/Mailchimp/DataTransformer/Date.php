<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\DataTransformer;

use Carbon\Carbon;

class Date implements MailchimpDataTransformerInterface
{
    /**
     * @var string
     */
    private $exportFormat;

    /**
     * @var string
     */
    private $importFormat;

    public function __construct($exportFormat = 'm/d/Y', $importFormat = 'Y-m-d')
    {
        $this->exportFormat = $exportFormat;
        $this->importFormat = $importFormat;
    }

    public function transformFromPimcoreToMailchimp($data)
    {
        if ($data instanceof \DateTime) {
            return $data->format($this->exportFormat);
        }
    }

    public function transformFromMailchimpToPimcore($data)
    {
        if(!$data) {
            return null;
        }

        return Carbon::createFromFormat($this->importFormat, $data);
    }

    public function didMergeFieldDataChange($pimcoreData, $mailchimpImportData)
    {
        if ($pimcoreData instanceof Carbon) {

            return $pimcoreData->format($this->importFormat) != $mailchimpImportData;
        }

        if(!$mailchimpImportData) {
            return false;
        }

        return true;
    }
}
