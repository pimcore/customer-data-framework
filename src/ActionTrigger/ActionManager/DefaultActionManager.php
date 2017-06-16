<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 23.11.2016
 * Time: 15:53
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\ActionManager;

use CustomerManagementFrameworkBundle\ActionTrigger\Action\ActionInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Action\ActionDefinitionInterface;
use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

class DefaultActionManager implements ActionManagerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function processAction(ActionDefinitionInterface $action, CustomerInterface $customer)
    {
        $this->logger->info(sprintf("process action ID %s", $action->getId()));

        if(class_exists($action->getImplementationClass())) {

            $actionImpl = Factory::getInstance()->createObject($action->getImplementationClass(), ActionInterface::class, ["logger"=>$this->logger]);

            $actionImpl->process($action, $customer);

        } else {
            $this->logger->error(sprintf("action implementation class %s not found", $action->getImplementationClass()));
        }
    }
}