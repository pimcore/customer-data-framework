<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 05.12.2016
 * Time: 14:32
 */

namespace CustomerManagementFramework\ActionTrigger\Condition;

use CustomerManagementFramework\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

interface ConditionInterface {

    public function __construct(LoggerInterface $logger);

    public function check(ConditionDefinitionInterface $conditionDefinition, CustomerInterface $customer);
}