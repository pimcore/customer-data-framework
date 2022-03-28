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

namespace CustomerManagementFrameworkBundle\Model;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;

abstract class AbstractActivity implements ActivityInterface
{
    protected $customer;

    /**
     * @return bool
     */
    public function cmfIsActive()
    {
        return true;
    }

    /**
     * @return Carbon
     */
    public function cmfGetActivityDate()
    {
        return new Carbon();
    }

    /**
     * @return bool
     */
    public function cmfUpdateOnSave()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function cmfWebserviceUpdateAllowed()
    {
        return !($this instanceof PersistentActivityInterface);
    }

    public function cmfUpdateData(array $data)
    {
        // TODO: Throw exception
    }

    /**
     * @param array $data
     * @param bool $fromWebservice
     *
     * @return bool
     */
    public static function cmfCreate(array $data, $fromWebservice = false)
    {
        return false;
    }

    public static function cmfGetAttributeDataTypes()
    {
        return false;
    }

    public static function cmfGetOverviewData(ActivityStoreEntryInterface $entry)
    {
        return false;
    }

    /**
     * @param ActivityStoreEntryInterface $entry
     *
     * @return array
     */
    public static function cmfGetDetailviewData(ActivityStoreEntryInterface $entry)
    {
        return $entry->getAttributes();
    }

    /**
     * @param ActivityStoreEntryInterface $entry
     *
     * @return bool
     */
    public static function cmfGetDetailviewTemplate(ActivityStoreEntryInterface $entry)
    {
        return false;
    }

    /**
     * @return CustomerInterface|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param CustomerInterface $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }
}
