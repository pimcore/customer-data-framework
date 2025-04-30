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

/**
 * Class Address
 *
 * Format allowed: https://us1.api.mailchimp.com/schema/3.0/Lists/Members/MergeField.json
 *
 * @package DobiBundle\Newsletter\ProviderHandler\Mailchimp\DataTransformer
 */
class Address implements MailchimpDataTransformerInterface
{
    /**
     * The address property the given value represents.
     *
     * @var string
     */
    protected $address_property;

    /**
     * Sets an empty state dummy if set to true.
     *
     * @var bool
     */
    protected $stateDummy = false;

    public function __construct($address_property, $stateDummy = false)
    {
        $this->address_property = $address_property;
        $this->stateDummy = $stateDummy;
    }

    public function transformFromPimcoreToMailchimp($data)
    {
        $data = [$this->address_property => $data];
        if ($this->stateDummy) {
            $data['state'] = '';
        }

        return $data;
    }

    public function transformFromMailchimpToPimcore($data)
    {
        if (!isset($data[$this->address_property])) {
            return null;
        }

        return $data[$this->address_property];
    }

    public function didMergeFieldDataChange($pimcoreData, $mailchimpImportData)
    {
        return !(!is_null($pimcoreData) && isset($mailchimpImportData[$this->address_property]) && $pimcoreData == $mailchimpImportData[$this->address_property]);
    }
}
