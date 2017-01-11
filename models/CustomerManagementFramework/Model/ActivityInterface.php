<?php

namespace CustomerManagementFramework\Model;

use Carbon\Carbon;
use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;

interface ActivityInterface {

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
     * Returns an array representation of this activity.
     *
     * @return array
     */
    public function cmfToArray();

    /**
     * Updates the data of the activity instance but doesn't save it.
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
     * Get the customer of the activity. A customer is required.
     *
     * @return CustomerInterface
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