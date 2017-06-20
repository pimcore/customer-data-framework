<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:43
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\EventHandler;


use CustomerManagementFrameworkBundle\ActionTrigger\Event\CustomerListEventInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Event\SingleCustomerEventInterface;
use Psr\Log\LoggerInterface;

interface EventHandlerInterface {

    public function handleEvent($event);

    public function handleSingleCustomerEvent(SingleCustomerEventInterface $event);

    public function handleCustomerListEvent(CustomerListEventInterface $event);
}