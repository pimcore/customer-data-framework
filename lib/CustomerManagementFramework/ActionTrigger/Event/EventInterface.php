<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:32
 */

namespace CustomerManagementFramework\ActionTrigger\Event;

use CustomerManagementFramework\ActionTrigger\Trigger\TriggerDefinitionInterface;

interface EventInterface {
    
    public function getName();
    public function appliesToTrigger(TriggerDefinitionInterface $trigger);
}