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

interface ActivityInterface
{
    const DATATYPE_STRING = 'string';
    const DATATYPE_INTEGER = 'integer';
    const DATATYPE_DOUBLE = 'double';
    const DATATYPE_BOOL = 'bool';

    /**
     * Returns if the activity is active. Only active activities are stored in the ActivityStore.
     *
     * @return bool
     */
    public function cmfIsActive();

    /**
     * Return the type of the activity (i.e. Booking, Login...)
     *
     * @return string
     */
    public function cmfGetType();

    /**
     * Return the date when the activity occured.
     *
     * @return Carbon
     */
    public function cmfGetActivityDate();

    /**
     * Returns an array representation of this activity. The return value/attributes array will be stored in the activity store.
     *
     * @return array
     */
    public function cmfToArray();

    /**
     * Returns an optional array with data types for each attribute which could be used by the ActivityStore to create columns/fields correctly based on given data types. (see self::DATATYPE_* constants)
     *
     * @return array|false
     */
    public static function cmfGetAttributeDataTypes();

    /**
     * Updates the data of the activity instance but doesn't save it.
     *
     * @param array $data
     *
     * @throws \Exception
     */
    public function cmfUpdateData(array $data);

    /**
     * Create an activity object instance from an array of data.
     *
     * @param array $data
     * @param bool $fromWebservice
     *
     * @return static|false
     */
    public static function cmfCreate(array $data, $fromWebservice = false);

    /**
     * Is update and creation of activities of this class via REST api allowed?
     *
     * @return bool
     */
    public function cmfWebserviceUpdateAllowed();

    /**
     * Get the customer of the activity. A customer is required.
     *
     * @return CustomerInterface|null
     */
    public function getCustomer();

    /**
     * Set the customer of the activity.
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function setCustomer($customer);

    /**
     * Returns an associative array with data which should be shown additional to the type and activity date within the ActivityView overview list.
     *
     * @param ActivityStoreEntryInterface $entry
     *
     * @return array|false
     */
    public static function cmfGetOverviewData(ActivityStoreEntryInterface $entry);

    /**
     * Returns an associative array with data which should be shown ActivityView detail page.
     *
     * @param ActivityStoreEntryInterface $entry
     *
     * @return array
     */
    public static function cmfGetDetailviewData(ActivityStoreEntryInterface $entry);

    /**
     * Optional: Returns a template file which should be used for the ActivityView detail page. With this it's possible to implement completely individual detail pages for each activity type.
     *
     * @param ActivityStoreEntryInterface $entry
     *
     * @return string|bool
     */
    public static function cmfGetDetailviewTemplate(ActivityStoreEntryInterface $entry);
}
