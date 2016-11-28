<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 23.11.2016
 * Time: 15:53
 */

namespace CustomerManagementFramework\ActionTrigger\ActionManager;

use CustomerManagementFramework\ActionTrigger\Rule;
use CustomerManagementFramework\ActionTrigger\Trigger\ActionDefinitionInterface;
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
    
    public function processAction(ActionDefinitionInterface $action)
    {
        $this->logger->debug(sprintf("process action ID %s", $action->getId()));
    }
}