<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 10.10.2016
 * Time: 11:22
 */

namespace CustomerManagementFramework\ActivityStoreEntry;

use CustomerManagementFramework\Model\IActivity;
use CustomerManagementFramework\Model\ICustomer;

class DefaultActivityStoreEntry implements IActivityStoreEntry {

    /**
     * @var int;
     */
    private $id;

    /**
     * @var ICustomer
     */
    private $customer;

    /**
     * @var int
     */
    private $activityDate;

    /**
     * @var string
     */
    private $type;

    /**
     * @var IActivity
     */
    private $relatedItem;

    /**
     * @var int
     */
    private $creationDate;

    /**
     * @var int
     */
    private $modificationDate;

    /**
     * @var $md5
     */
    private $md5;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return ICustomer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param ICustomer $customer
     */
    public function setCustomer(ICustomer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return int
     */
    public function getActivityDate()
    {
        return $this->activityDate;
    }

    /**
     * @param int $activityDate
     */
    public function setActivityDate($activityDate)
    {
        $this->activityDate = $activityDate;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return IActivity
     */
    public function getRelatedItem()
    {
        return $this->relatedItem;
    }

    /**
     * @param IActivity $relatedItem
     */
    public function setRelatedItem(IActivity $relatedItem)
    {
        $this->relatedItem = $relatedItem;
    }

    /**
     * @return int
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return int
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param int $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @return string
     */
    public function getMd5()
    {
        return $this->md5;
    }

    /**
     * @param string $md5
     */
    public function setMd5($md5)
    {
        $this->md5 = $md5;
    }

    

}