<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 13:37
 */

namespace CustomerManagementFramework\Model;

use Carbon\Carbon;

abstract class AbstractActivity extends \Pimcore\Model\Object\Concrete implements IActivity {

    public function cmfGetActivityDate()
    {
        return Carbon::createFromTimestamp($this->getCreationDate());
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

        if($customer = $this->getCustomer()) {
            $result['customer'] = $customer->cmfToArray();
        }

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


    public function addCmfActivityId($cmfActivityId)
    {
        $ids = $this->getCmfActivityIds() . ',' . $cmfActivityId;
        $this->setCmfActivityIds($ids);
    }


}