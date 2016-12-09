<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 13:16
 */

namespace CustomerManagementFramework\ActionTrigger\Action;

use CustomerManagementFramework\ActionTrigger\ActionDefinition;
use Psr\Log\LoggerInterface;

abstract class AbstractAction implements ActionInterface{

    protected $logger;

    protected static $actionDelayMultiplier = [
        'm' => 1,
        'h' => 60,
        'd' => 60*24
    ];
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    public static function createActionDefinitionFromEditmode(\stdClass $setting)
    {
        $actionDelayMultiplier = isset(self::$actionDelayMultiplier[$setting->options->actionDelayGuiType]) ? self::$actionDelayMultiplier[$setting->options->actionDelayGuiType] : 1;

        $action = new \CustomerManagementFramework\ActionTrigger\ActionDefinition();
        $action->setId($setting->id);
        $action->setCreationDate($setting->creationDate);
        $action->setOptions(json_decode(json_encode($setting->options), true));
        $action->setImplementationClass($setting->implementationClass);
        $action->setActionDelay($setting->options->actionDelayGuiValue * $actionDelayMultiplier);

        return $action;
    }

    public static function getDataForEditmode(ActionDefinitionInterface $actionDefinition)
    {
        return $actionDefinition->toArray();
    }
}