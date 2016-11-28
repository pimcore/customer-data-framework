<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 16:33
 */

namespace CustomerManagementFramework\ActionTrigger\Trigger;

interface TriggerDefinitionInterface
{

    public function __construct(array $definitionData);

    public function getEventName();

    public function getDefinitionData();

    public function getOptions();
}