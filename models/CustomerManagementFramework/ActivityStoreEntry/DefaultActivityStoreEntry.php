<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 10.10.2016
 * Time: 11:22
 */

namespace CustomerManagementFramework\ActivityStoreEntry;

use Carbon\Carbon;
use CustomerManagementFramework\Helper\Json;
use CustomerManagementFramework\Model\ActivityInterface;
use CustomerManagementFramework\Model\CustomerInterface;

class DefaultActivityStoreEntry implements ActivityStoreEntryInterface {


    /**
     * @var int;
     */
    private $id;

    /**
     * @var CustomerInterface
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
     * @var ActivityInterface
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
     * @var int|null
     */
    private $o_id;

    /**
     * @var int|null
     */
    private $a_id;

    /**
     * @var string
     */
    private $implementationClass;


    /**
     * @var $attributes
     */
    private $attributes;

    public function __construct($data) {

        $this->setId($data['id']);
        $this->setActivityDate($data['activityDate']);
        $this->setType($data['type']);
        $this->setImplementationClass($data['implementationClass']);
        $this->setAttributes(is_array($data['attributes']) ? $data['attributes'] : \Zend_Json::decode(Json::cleanUpJson($data['attributes'])));
        $this->setMd5($data['md5']);
        $this->setCreationDate($data['creationDate']);
        $this->setModificationDate($data['modificationDate']);
        $this->o_id = $data['o_id'];
        $this->a_id = $data['a_id'];
    }

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
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param CustomerInterface $customer
     */
    public function setCustomer(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return Carbon
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
        $this->activityDate = Carbon::createFromTimestamp($activityDate);
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
     * @return ActivityInterface
     */
    public function getRelatedItem()
    {
        return $this->relatedItem;
    }

    /**
     * @param ActivityInterface $relatedItem
     */
    public function setRelatedItem(ActivityInterface $relatedItem)
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

    /**
     * @return string
     */
    public function getImplementationClass()
    {
        return $this->implementationClass;
    }

    /**
     * @param string $implementationClass
     */
    public function setImplementationClass($implementationClass)
    {
        $this->implementationClass = $implementationClass;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    
    
}