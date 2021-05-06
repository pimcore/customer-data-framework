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

namespace CustomerManagementFrameworkBundle\ActionTrigger\ActionManager;

use CustomerManagementFrameworkBundle\ActionTrigger\Action\ActionDefinitionInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Action\ActionInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\GDPR\Consent\ConsentCheckerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;

class DefaultActionManager implements ActionManagerInterface
{
    use LoggerAware;

    /**
     * @var ConsentCheckerInterface
     */
    protected $consentChecker;

    public function __construct(ConsentCheckerInterface $consentChecker)
    {
        $this->consentChecker = $consentChecker;
    }

    public function processAction(
        ActionDefinitionInterface $action,
        CustomerInterface $customer,
        RuleEnvironmentInterface $environment
    ) {
        $this->getLogger()->info(sprintf('process action ID %s', $action->getId()));

        if (class_exists($action->getImplementationClass())) {
            /** @var ActionInterface $actionImpl */
            $actionImpl = Factory::getInstance()->createObject(
                $action->getImplementationClass(),
                ActionInterface::class,
                ['logger' => $this->getLogger(), $this->consentChecker]
            );

            $actionImpl->process($action, $customer, $environment);
        } else {
            $this->getLogger()->error(
                sprintf('action implementation class %s not found', $action->getImplementationClass())
            );
        }
    }
}
