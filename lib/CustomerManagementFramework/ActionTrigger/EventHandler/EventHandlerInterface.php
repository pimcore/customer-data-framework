<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:43
 */

namespace CustomerManagementFramework\ActionTrigger\EventHandler;


use CustomerManagementFramework\ActionTrigger\Event\EventInterface;

interface EventHandlerInterface {

    public function handleEvent(\Zend_EventManager_Event $e, EventInterface $event);
}