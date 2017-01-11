<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:48
 */

namespace CustomerManagementFramework\ActionTrigger\Queue;

use CustomerManagementFramework\ActionTrigger\Action\ActionDefinitionInterface;
use CustomerManagementFramework\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

interface QueueInterface {

    public function __construct(LoggerInterface $logger);

    public function addToQueue(ActionDefinitionInterface $action, CustomerInterface $customer);

    public function processQueue();
}