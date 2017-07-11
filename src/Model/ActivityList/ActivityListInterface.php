<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 13:39
 */

namespace CustomerManagementFrameworkBundle\Model\ActivityList;

use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Paginator\AdapterAggregateInterface;

interface ActivityListInterface extends AdapterInterface, AdapterAggregateInterface, \Iterator
{

    public function setCondition($condition, $conditionVariables = null);

}