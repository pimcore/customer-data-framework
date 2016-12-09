<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 23.11.2016
 * Time: 15:53
 */

namespace CustomerManagementFramework\ActionTrigger\ActionManager;

use CustomerManagementFramework\ActionTrigger\Action\ActionDefinitionInterface;
use CustomerManagementFramework\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

interface ActionManagerInterface
{
    public function __construct(LoggerInterface $logger);

    public function processAction(ActionDefinitionInterface $action, CustomerInterface $customer);
}