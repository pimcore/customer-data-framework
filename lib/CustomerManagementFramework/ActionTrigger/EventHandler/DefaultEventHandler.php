<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:43
 */

namespace CustomerManagementFramework\ActionTrigger\EventHandler;


use CustomerManagementFramework\ActionTrigger\Event\EventInterface;

class DefaultEventHandler implements EventHandlerInterface{

    private $rules;

    public function __construct()
    {
        $this->rules = [

        ];
    }

    public function handleEvent(\Zend_EventManager_Event $e, EventInterface $event)
    {

        print "an event occured";

        var_dump($e);
    }
}