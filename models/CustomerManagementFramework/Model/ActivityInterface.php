<?php

namespace CustomerManagementFramework\Model;

use Carbon\Carbon;
use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;

interface ActivityInterface {

    /**
     * @return bool
     */
    public function cmfIsActive();

    public function cmfUpdateOnSave();

    /**
     * @return string
     */
    public function cmfGetType();

    /**
     * @return Carbon
     */
    public function cmfGetActivityDate();
    
    /**
     * Returns an array representation of this activity.
     *
     * @return array
     */
    public function cmfToArray();

    /**
     *
     * @param array $data
     *
     * @return bool
     */
    public function cmfUpdateData(array $data);

    /**
     * Create an activity object instance from an array of data.
     *
     * @param array $data
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
     * @return CustomerInterface
     */
    public function getCustomer();

    /**
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
     * @return array
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