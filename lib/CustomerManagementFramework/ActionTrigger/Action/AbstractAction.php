<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 13:16
 */

namespace CustomerManagementFramework\ActionTrigger\Action;

use CustomerManagementFramework\ActionTrigger\Trigger\ActionDefinitionInterface;
use CustomerManagementFramework\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractAction implements ActionInterface{

    protected $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}