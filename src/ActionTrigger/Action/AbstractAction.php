<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Action;

use CustomerManagementFrameworkBundle\GDPR\Consent\ConsentCheckerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractAction implements ActionInterface
{
    protected $logger;

    /**
     * @var ConsentCheckerInterface
     */
    protected $consentChecker;

    protected static $actionDelayMultiplier = [
        'm' => 1,
        'h' => 60,
        'd' => 60 * 24,
    ];

    public function __construct(LoggerInterface $logger, ConsentCheckerInterface $consentChecker)
    {
        $this->logger = $logger;
        $this->consentChecker = $consentChecker;
    }

    public static function createActionDefinitionFromEditmode(\stdClass $setting)
    {
        $actionDelayMultiplier = isset(self::$actionDelayMultiplier[$setting->options->actionDelayGuiType]) ? self::$actionDelayMultiplier[$setting->options->actionDelayGuiType] : 1;

        $action = new \CustomerManagementFrameworkBundle\Model\ActionTrigger\ActionDefinition();
        if (isset($setting->id)) {
            $action->setId($setting->id);
        }
        if (isset($setting->creationDate)) {
            $action->setCreationDate($setting->creationDate);
        }
        $action->setOptions(json_decode(json_encode($setting->options), true));
        $action->setImplementationClass($setting->implementationClass);
        $action->setActionDelay($setting->options->actionDelayGuiValue * $actionDelayMultiplier);

        return $action;
    }

    public static function getDataForEditmode(ActionDefinitionInterface $actionDefinition)
    {
        return $actionDefinition->toArray();
    }

    public function getConsentChecker(): ConsentCheckerInterface
    {
        return $this->consentChecker;
    }
}
