<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:43
 */

namespace CustomerManagementFramework\ActionTrigger\EventHandler;


use CustomerManagementFramework\ActionTrigger\Event\CustomerListEventInterface;
use CustomerManagementFramework\ActionTrigger\Event\SingleCustomerEventInterface;
use Psr\Log\LoggerInterface;

interface EventHandlerInterface {

    public function __construct(LoggerInterface $logger);

    public function handleSingleCustomerEvent(\Zend_EventManager_Event $e, SingleCustomerEventInterface $event);

    public function handleCustomerListEvent(\Zend_EventManager_Event $e, CustomerListEventInterface $event);
}