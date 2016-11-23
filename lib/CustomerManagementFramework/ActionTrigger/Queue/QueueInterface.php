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
use Psr\Log\LoggerInterface;

interface QueueInterface {

    public function __construct(LoggerInterface $logger);

    public function addToQueue(Rule $rule, EventInterface $event);

    public function processQueue();
}