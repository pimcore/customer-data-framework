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
