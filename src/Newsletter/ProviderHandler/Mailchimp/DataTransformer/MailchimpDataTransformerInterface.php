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

interface MailchimpDataTransformerInterface
{
    public function transformFromPimcoreToMailchimp($data);

    public function transformFromMailchimpToPimcore($data);

    /**
     * @param mixed $pimcoreData
     * @param mixed $mailchimpImportData
     */
    public function didMergeFieldDataChange($pimcoreData, $mailchimpImportData);
}
