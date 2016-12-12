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

abstract class AbstractActivity extends \Pimcore\Model\Object\Concrete implements ActivityInterface {

    public function cmfIsActive() {
        return $this->getPublished() && ($this->getCustomer() instanceof CustomerInterface);
    }

    public function cmfGetActivityDate()
    {
        return Carbon::createFromTimestamp($this->getCreationDate());
    }

    public function cmfUpdateOnSave()
    {
        return true;
    }

    /**
     * @return string
     */
    public function cmfGetType()
    {
        return $this->getClassName();
    }

    public function cmfToArray()
    {
        $fieldDefintions = $this->getClass()->getFieldDefinitions();

        $result = [];

        foreach($fieldDefintions as $fd)
        {
            $fieldName = $fd->getName();
            $result[$fieldName] = $fd->getForWebserviceExport($this);
        }

        unset($result['customer']);

        $result['o_id']  = $this->getId();
        $result['o_key'] = $this->getKey();

        return $result;
    }

    public function cmfUpdateData(array $data)
    {
        // TODO: Implement cmfUpdateDate() method.
    }

    public static function cmfCreate(array $data)
    {
        // TODO: Implement cmfCreate() method.
    }

    public static function cmfGetOverviewData(ActivityStoreEntryInterface $entry)
    {
        return false;
    }

    public static function cmfGetDetailviewData(ActivityStoreEntryInterface $entry)
    {
        $attributes = $entry->getAttributes();

        return Factory::getInstance()->getActivityView()->formatAttributes($entry->getImplementationClass(), $attributes);
    }

    public static function cmfGetDetailviewTemplate(ActivityStoreEntryInterface $entry)
    {
        return false;
    }


}