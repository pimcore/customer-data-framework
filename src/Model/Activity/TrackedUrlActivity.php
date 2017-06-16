<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.12.2016
 * Time: 17:39
 */

namespace CustomerManagementFrameworkBundle\Model\Activity;

use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Model\AbstractActivity;
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
            'code' => $this->activityDefinition->getCode()
        ];

        if($additionalAttributes = $this->activityDefinition->getAttributes()) {
            foreach($additionalAttributes as $additionalAttribute) {
                $attributes[$additionalAttribute['attribute']->getData()] = $additionalAttribute['attributeValue']->getData();
            }

        }


        return $attributes;
    }

    public static function cmfGetOverviewData(ActivityStoreEntryInterface $entry)
    {
        return $entry->getAttributes();
    }
}