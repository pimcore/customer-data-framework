<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 13:37
 */

namespace CustomerManagementFramework\Model\Activity;

use Carbon\Carbon;
use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\ActivityInterface;
use CustomerManagementFramework\Model\CustomerInterface;

class GenericActivity implements ActivityInterface {

    protected $customer;

    /**
     * @var array
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function cmfIsActive() {
        return true;
    }

    public function cmfGetActivityDate()
    {
        if(empty($this->data['activityDate'])) {
            return new Carbon();
        }

        if($this->data['activityDate'] instanceof Carbon) {
            return $this->data['activityDate'];
        }

        if(is_int($this->data['activityDate'])) {
            return Carbon::createFromTimestamp($this->data['activityDate']);
        }

        return new Carbon();
    }

    public function cmfUpdateOnSave()
    {
        return false;
    }

    public function cmfWebserviceUpdateAllowed()
    {
        return true;
    }

    public function cmfUpdateData(array $data)
    {
        $this->data = array_merge((array)$this->data, $data);
    }

    public static function cmfCreate(array $data, $fromWebservice = false)
    {
        return new static($data);
    }

    public function cmfGetType()
    {
        return $this->data['type'];
    }

    public function cmfToArray()
    {
        return $this->data['attributes'];
    }


    public static function cmfGetOverviewData(ActivityStoreEntryInterface $entry)
    {
        return false;
    }

    public static function cmfGetDetailviewData(ActivityStoreEntryInterface $entry)
    {
        return $entry->getAttributes();
    }

    public static function cmfGetDetailviewTemplate(ActivityStoreEntryInterface $entry)
    {
        return false;
    }

    public function getCustomer()
    {
        if(empty($this->customer)) {
            $this->customer = Factory::getInstance()->getCustomerProvider()->getById($this->data['customerId']);
        }

        return $this->customer;
    }

    public function setCustomer($customer)
    {
        if($customer instanceof CustomerInterface) {
            $this->customer = $customer;
            $this->data['customer'] = $customer->getId();
        } elseif(is_int($this->data['customer'])) {
            $this->customer = null;
            $this->data['customer'] = $customer;
        } else {
            throw new \Exception('invalid customer');
        }

    }


}