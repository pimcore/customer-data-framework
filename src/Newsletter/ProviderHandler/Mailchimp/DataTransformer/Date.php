<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
        if (!$data) {
            return null;
        }

        return Carbon::createFromFormat($this->importFormat, $data);
    }

    public function didMergeFieldDataChange($pimcoreData, $mailchimpImportData)
    {
        if ($pimcoreData instanceof Carbon) {
            return $pimcoreData->format($this->importFormat) != $mailchimpImportData;
        }

        if (!$mailchimpImportData) {
            return false;
        }

        return true;
    }
}
