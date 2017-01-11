<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 13:37
 */

namespace CustomerManagementFramework\Model;

use Carbon\Carbon;
use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFramework\Factory;
use Pimcore\Model\Object\Booking;
use Pimcore\Model\Object\ClassDefinition;
use Pimcore\Translate\Admin;

abstract class AbstractActivity implements ActivityInterface {

    protected $customer;

    public function cmfIsActive() {
        return true;
    }

    public function cmfGetActivityDate()
    {
        return new Carbon();
    }

    public function cmfUpdateOnSave()
    {
        return true;
    }

    public function cmfWebserviceUpdateAllowed()
    {
        return !($this instanceof PersistentActivityInterface);
    }

    public function cmfUpdateData(array $data)
    {
        return false;
    }

    public static function cmfCreate(array $data, $fromWebservice = false)
    {
        return false;
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
        return $this->customer;
    }

    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }


}