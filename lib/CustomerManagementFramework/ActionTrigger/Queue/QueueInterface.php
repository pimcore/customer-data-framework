<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:48
 */

namespace CustomerManagementFramework\ActionTrigger\Queue;

use CustomerManagementFramework\ActionTrigger\Event\EventInterface;
use CustomerManagementFramework\ActionTrigger\Rule;
use CustomerManagementFramework\ActionTrigger\Action\ActionDefinitionInterface;
use Psr\Log\LoggerInterface;

interface QueueInterface {

    public function __construct(LoggerInterface $logger);

    public function addToQueue(ActionDefinitionInterface $action, EventInterface $event);

    public function processQueue();
}