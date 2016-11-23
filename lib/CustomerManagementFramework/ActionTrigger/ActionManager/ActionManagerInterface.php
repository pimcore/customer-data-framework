<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 23.11.2016
 * Time: 15:53
 */

namespace CustomerManagementFramework\ActionTrigger\ActionManager;

use CustomerManagementFramework\ActionTrigger\Rule;
use Psr\Log\LoggerInterface;

interface ActionManagerInterface
{
    public function __construct(LoggerInterface $logger);

    public function processActions(Rule $rule);
}