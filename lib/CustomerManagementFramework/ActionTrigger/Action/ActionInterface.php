<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 13:16
 */

namespace CustomerManagementFramework\ActionTrigger\Action;

use CustomerManagementFramework\ActionTrigger\ActionDefinition;
use CustomerManagementFramework\ActionTrigger\Trigger\ActionDefinitionInterface;
use CustomerManagementFramework\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

interface ActionInterface {

    public function __construct(LoggerInterface $logger);

    public function process(ActionDefinitionInterface $actionDefinition, CustomerInterface $customer);

    public static function createActionDefinitionFromEditmode(\stdClass $data);

    public static function getDataForEditmode(ActionDefinitionInterface $actionDefinition);
}