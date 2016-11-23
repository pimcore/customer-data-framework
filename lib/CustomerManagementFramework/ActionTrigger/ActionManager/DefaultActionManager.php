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
    
    public function processActions(Rule $rule)
    {
        $this->logger->debug(sprintf("process actions for rule ID %s", $rule->getId()));
    }
}