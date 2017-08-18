<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 16:44
 */

namespace CustomerManagementFrameworkBundle\Model\ActionTrigger;

use CustomerManagementFrameworkBundle\ActionTrigger\Trigger\TriggerDefinitionInterface;

class TriggerDefinition implements TriggerDefinitionInterface
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

    public function toArray()
    {
        return $this->getDefinitionData();
    }
}
