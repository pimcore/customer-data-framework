<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Model\Activity;

use CustomerManagementFrameworkBundle\Model\AbstractActivity;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\Object\ActivityDefinition;

class TrackedUrlActivity extends AbstractActivity
{
    protected $customer;
    private $activityDefinition;

    public function __construct(CustomerInterface $customer, ActivityDefinition $activityDefinition)
    {
        $this->customer = $customer;
        $this->activityDefinition = $activityDefinition;
    }

    public function cmfGetType()
    {
        return $this->activityDefinition->getAttributeType();
    }

    public function cmfToArray()
    {
        $attributes = [
            'label' => $this->activityDefinition->getLabel(),
            'code' => $this->activityDefinition->getCode(),
        ];

        if ($additionalAttributes = $this->activityDefinition->getAttributes()) {
            foreach ($additionalAttributes as $additionalAttribute) {
                $attributes[$additionalAttribute['attribute']->getData(
                )] = $additionalAttribute['attributeValue']->getData();
            }
        }

        return $attributes;
    }

    public static function cmfGetOverviewData(ActivityStoreEntryInterface $entry)
    {
        return $entry->getAttributes();
    }

    public function cmfWebserviceUpdateAllowed()
    {
        return true;
    }

    /**
     * @param array $data
     * @param bool $fromWebservice
     *
     * @return bool
     */
    public static function cmfCreate(array $data, $fromWebservice = false)
    {
        return new static(
            \Pimcore::getContainer()->get('cmf.customer_provider')->getById($data['customerId']),
            ActivityDefinition::getById('6697057')
        );
    }
}
