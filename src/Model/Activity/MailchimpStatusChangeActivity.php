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

class MailchimpStatusChangeActivity extends AbstractActivity
{
    protected $customer;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var array
     */
    protected $additionalAttributes = [];

    /**
     * @var int
     */
    protected $activityDate;

    const TYPE = 'Mailchimp status change';

    /**
     * MailchimpStatusChangeActivity constructor.
     *
     * @param CustomerInterface $customer
     * @param string $status
     * @param int $activityDate
     */
    public function __construct(CustomerInterface $customer, $status, array $additionalAttributes = [], $activityDate = null)
    {
        if (is_null($activityDate)) {
            $activityDate = time();
        }

        $this->customer = $customer;
        $this->status = $status;
        $this->additionalAttributes = $additionalAttributes;
        $this->activityDate = $activityDate;
    }

    public function cmfGetType()
    {
        return self::TYPE;
    }

    /**
     * @inheritdoc
     */
    public function cmfToArray()
    {
        $attributes = $this->additionalAttributes;
        $attributes['status'] = $this->status;

        return $attributes;
    }

    public static function cmfGetOverviewData(ActivityStoreEntryInterface $entry)
    {
        return ['status' => $entry->getAttributes()['status'], 'shortcut' => $entry->getAttributes()['shortcut'], 'listId' => $entry->getAttributes()['listId']];
    }

    public function cmfWebserviceUpdateAllowed()
    {
        return false;
    }

    /**
     * @param array $data
     * @param bool $fromWebservice
     *
     * @return false
     */
    public static function cmfCreate(array $data, $fromWebservice = false)
    {
        return false;
    }
}
