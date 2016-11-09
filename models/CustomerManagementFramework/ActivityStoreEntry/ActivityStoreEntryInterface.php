<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 12.10.2016
 * Time: 13:30
 */

namespace CustomerManagementFramework\ActivityStoreEntry;

use CustomerManagementFramework\Model\ActivityInterface;
use CustomerManagementFramework\Model\CustomerInterface;

interface ActivityStoreEntryInterface {

    public function __construct($data);

    public function getId();
    public function setId($id);
    public function getCustomer();
    public function setCustomer(CustomerInterface $customer);
    public function getActivityDate();
    public function setActivityDate($timestamp);
    public function getType();
    public function setType($type);
    public function getRelatedItem();
    public function setRelatedItem(ActivityInterface $item);
    public function getCreationDate();
    public function setCreationDate($timestamp);
    public function getModificationDate();
    public function setModificationDate($timestamp);
    public function getMd5();
    public function setMd5($md5);
    public function getImplementationClass();
    public function setImplementationClass($implementationClass);
    public function getAttributes();
    public function setAttributes(array $attributes);
}