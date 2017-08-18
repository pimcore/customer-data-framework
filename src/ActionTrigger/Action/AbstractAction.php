<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Action;

use Psr\Log\LoggerInterface;

abstract class AbstractAction implements ActionInterface
{
    protected $logger;

    protected static $actionDelayMultiplier = [
        'm' => 1,
        'h' => 60,
        'd' => 60 * 24,
    ];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function createActionDefinitionFromEditmode(\stdClass $setting)
    {
        $actionDelayMultiplier = isset(self::$actionDelayMultiplier[$setting->options->actionDelayGuiType]) ? self::$actionDelayMultiplier[$setting->options->actionDelayGuiType] : 1;

        $action = new \CustomerManagementFrameworkBundle\Model\ActionTrigger\ActionDefinition();
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
