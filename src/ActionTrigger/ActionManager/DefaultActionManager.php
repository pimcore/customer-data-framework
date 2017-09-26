<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\ActionManager;

use CustomerManagementFrameworkBundle\ActionTrigger\Action\ActionDefinitionInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Action\ActionInterface;
use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;

class DefaultActionManager implements ActionManagerInterface
{
    use LoggerAware;

    public function processAction(ActionDefinitionInterface $action, CustomerInterface $customer)
    {
        $this->getLogger()->info(sprintf('process action ID %s', $action->getId()));

        if (class_exists($action->getImplementationClass())) {
            $actionImpl = Factory::getInstance()->createObject(
                $action->getImplementationClass(),
                ActionInterface::class,
                ['logger' => $this->getLogger()]
            );

            $actionImpl->process($action, $customer);
        } else {
            $this->getLogger()->error(
                sprintf('action implementation class %s not found', $action->getImplementationClass())
            );
        }
    }
}
