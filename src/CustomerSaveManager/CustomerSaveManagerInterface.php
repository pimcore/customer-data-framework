<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:10
 */

namespace CustomerManagementFrameworkBundle\CustomerSaveManager;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

interface CustomerSaveManagerInterface
{

    /**
     * @param CustomerInterface $customer
     * @return void
     */
    public function preAdd(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     * @return void
     */
    public function preUpdate(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     * @return void
     */
    public function postUpdate(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     * @return void
     */
    public function preDelete(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     * @return void
     */
    public function postDelete(CustomerInterface $customer);

    /**
     * @param bool $segmentBuildingHookEnabled
     * @return void
     */
    public function setSegmentBuildingHookEnabled($segmentBuildingHookEnabled);

    /**
     * @return bool
     */
    public function getSegmentBuildingHookEnabled();

    /**
     * @return bool
     */
    public function getCustomerSaveValidatorEnabled();

    /**
     * @param bool $customerSaveValidatorEnabled
     */
    public function setCustomerSaveValidatorEnabled($customerSaveValidatorEnabled);

    /**
     * @param CustomerInterface $customer
     * @param bool $withDuplicatesCheck
     * @return bool
     */
    function validateOnSave(CustomerInterface $customer, $withDuplicatesCheck = true);

    /**
     * Saves customer with disabled segment builder + customer save validator
     * @param CustomerInterface $customer
     * @param bool $disableVersions
     * @return void
     */
    function saveWithDisabledHooks(CustomerInterface $customer, $disableVersions = false);

    /**
     * Dirty / quick save customer w/o invoking any hooks, save-handlers, version and alike
     * @param CustomerInterface $customer
     * @return void
     */
    function saveDirty(CustomerInterface $customer);
}