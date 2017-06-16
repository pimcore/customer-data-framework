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

    public function preAdd(CustomerInterface $customer);

    public function preUpdate(CustomerInterface $customer);
    
    public function postUpdate(CustomerInterface $customer);

    public function preDelete(CustomerInterface $customer);

    public function postDelete(CustomerInterface $customer);

    public function setSegmentBuildingHookEnabled($segmentBuildingHookEnabled);

    public function getSegmentBuildingHookEnabled();

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
     * @param CustomerInterface $customer
     * @param bool $disableVersions
     * @return mixed
     */
    function saveWithDisabledHooks(CustomerInterface $customer, $disableVersions = false );

    /**
     * Dirty / quick save customer w/o invoking any hooks, save-handlers, version and alike
     * @param CustomerInterface $customer
     * @return mixed
     */
    function saveDirty( CustomerInterface $customer );
}