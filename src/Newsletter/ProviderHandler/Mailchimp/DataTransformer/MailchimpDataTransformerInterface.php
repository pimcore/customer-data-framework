<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
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
