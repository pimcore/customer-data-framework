<?php

namespace CustomerManagementFramework\Model;

use Carbon\Carbon;

interface IActivity {

    /**
     * @return bool
     */
    public function cmfIsActive();

    /**
     * @return string
     */
    public function cmfGetType();

    /**
     * @return Carbon
     */
    public function cmfGetActivityDate();
    
    /**
     * @return array
     */
    public function cmfToArray();

    /**
     * @param array $data
     *
     * @return bool
     */
    public function cmfUpdateData(array $data);

    /**
     * @param array $data
     *
     * @return static
     */
    public static function cmfCreate(array $data);


    /**
     * @return ICustomer
     */
    public function getCustomer();

    public function getCmfActivityIds();
    public function addCmfActivityId($cmfActivityId);
    public function setCmfActivityIds($cmfActivityIds);
    public function save();

    /**
     * @param ICustomer $customer
     *
     * @return void
     */
    public function setCustomer($customer);
}