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
use Pimcore\Model\DataObject\LinkActivityDefinition;


class TrackedUrlActivity extends AbstractActivity
{
    protected $customer;
    private $activityDefinition;

    public function __construct(CustomerInterface $customer, LinkActivityDefinition $activityDefinition)
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
            'activityDefinitionId' => $this->activityDefinition->getId(),
        ];

        if ($additionalAttributes = $this->activityDefinition->getAttributes()) {
            foreach ($additionalAttributes as $additionalAttribute) {
                $attributes[$additionalAttribute['attribute']->getData()] = $additionalAttribute['attributeValue']->getData();
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
        return false;
    }

    /**
     * @param array $data
     * @param bool $fromWebservice
     *
     * @return static|null
     */
    public static function cmfCreate(array $data, $fromWebservice = false)
    {
        if(!isset($data['activityDefinitionId'])) {
            return null;
        }

        return new static(
            \Pimcore::getContainer()->get('cmf.customer_provider')->getById($data['customerId']),
            LinkActivityDefinition::getById($data['activityDefinitionId'])
        );
    }
}
