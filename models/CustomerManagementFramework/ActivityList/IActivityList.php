<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 13:39
 */

namespace CustomerManagementFramework\ActivityList;

interface IActivityList extends \Zend_Paginator_Adapter_Interface, \Zend_Paginator_AdapterAggregate, \Iterator //\Zend_Paginator_Adapter_Interface, \Zend_Paginator_AdapterAggregate, \Iterator
{
    
}