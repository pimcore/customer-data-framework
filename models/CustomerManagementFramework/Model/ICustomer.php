<?php

namespace CustomerManagementFramework\Model;

interface ICustomer {

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
     * @return array
     */
    public function cmfToArray();
}