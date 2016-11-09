<?php

namespace CustomerManagementFramework\Model;

use Pimcore\Model\Object\CustomerSegment;

interface CustomerInterface {

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getGender();

    /**
     * @return string
     */
    public function getFirstname();

    /**
     * @return string
     */
    public function getLastname();

    /**
     * @return string
     */
    public function getStreet();

    /**
     * @return string
     */
    public function getZip();

    /**
     * @return string
     */
    public function getCity();

    /**
     * @return string
     */
    public function getCountryCode();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getPhone();

    /**
     * @return CustomerSegment[]
     */
    public function getManualSegments();

    /**
     * @return CustomerSegment[]
     */
    public function getCalculatedSegments();

    /**
     * @return CustomerSegment[]
     */
    public function getAllSegments();

    /**
     * @return array
     */
    public function cmfToArray();
}