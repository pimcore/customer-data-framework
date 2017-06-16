<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 13:39
 */

namespace CustomerManagementFrameworkBundle\Model\ActivityList;

use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;

interface ActivityListInterface extends \Zend_Paginator_Adapter_Interface, \Zend_Paginator_AdapterAggregate, \Iterator //\Zend_Paginator_Adapter_Interface, \Zend_Paginator_AdapterAggregate, \Iterator
{

    public function setCondition($condition, $conditionVariables = null);

}