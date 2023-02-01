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
use Pimcore\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;

class TargetGroupAssignActivity extends AbstractActivity
{
    const TYPE = 'Target Group Assignment';

    /**
     * @var TargetGroup
     */
    protected $targetGroup;

    /**
     * @var int
     */
    protected $weight;

    /**
     * @var int
     */
    protected $totalWeight;

    /**
     * @var int|null
     */
    protected $activityDate;

    /**
     * TargetGroupAssignActivity constructor.
     *
     * @param CustomerInterface $customer
     * @param TargetGroup $targetGroup
     * @param int $weight
     * @param int $totalWeight
     * @param int|null $activityDate
     */
    public function __construct(CustomerInterface $customer, TargetGroup $targetGroup, $weight, $totalWeight, $activityDate = null)
    {
        if (is_null($activityDate)) {
            $activityDate = time();
        }

        $this->customer = $customer;
        $this->targetGroup = $targetGroup;
        $this->weight = $weight;
        $this->totalWeight = $totalWeight;
        $this->activityDate = $activityDate;
    }

    /**
     * Return the type of the activity (i.e. Booking, Login...)
     *
     * @return string
     */
    public function cmfGetType()
    {
        return self::TYPE;
    }

    /**
     * @inheritdoc
     */
    public function cmfToArray()
    {
        return ['targetGroup' => $this->targetGroup->getId(), 'targetGroupName' => $this->targetGroup->getName(), 'weight' => $this->weight, 'totalWeight' => $this->totalWeight];
    }

    /**
     * @inheritdoc
     */
    public static function cmfGetOverviewData(ActivityStoreEntryInterface $entry)
    {
        return $entry->getAttributes();
    }

    /**
     * @return bool
     */
    public function cmfWebserviceUpdateAllowed()
    {
        return false;
    }
}
