<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 16:44
 */

namespace CustomerManagementFramework\ActionTrigger;

use CustomerManagementFramework\ActionTrigger\Trigger\TriggerInterface;

class Trigger implements TriggerInterface
{
    private $definitionData;

    private $eventName;

    private $options;

    public function __construct(array $definitionData)
    {
        $this->definitionData = $definitionData;
        $this->eventName = $definitionData['eventName'];
        $this->options = isset($definitionData['options']) ? $definitionData['options'] : [];
    }

    public function getEventName()
    {
        return $this->eventName;
    }

    public function getDefinitionData()
    {
        return $this->definitionData;
    }

    public function getOptions()
    {
        return $this->options;
    }
}