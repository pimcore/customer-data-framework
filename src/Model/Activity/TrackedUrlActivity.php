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

    /**
     * @inheritdoc
     */
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
     * @return static|false
     */
    public static function cmfCreate(array $data, $fromWebservice = false)
    {
        if (!isset($data['activityDefinitionId']) || LinkActivityDefinition::getById($data['activityDefinitionId']) == null) {
            return false;
        }

        return new static(
            \Pimcore::getContainer()->get('cmf.customer_provider')->getById($data['customerId']),
            LinkActivityDefinition::getById($data['activityDefinitionId'])
        );
    }
}
