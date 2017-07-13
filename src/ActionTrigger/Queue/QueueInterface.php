<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:48
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Queue;

use CustomerManagementFrameworkBundle\ActionTrigger\Action\ActionDefinitionInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

interface QueueInterface
{

    public function addToQueue(ActionDefinitionInterface $action, CustomerInterface $customer);

    public function processQueue();
}