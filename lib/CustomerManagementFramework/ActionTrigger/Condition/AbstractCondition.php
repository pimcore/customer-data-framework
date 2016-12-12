<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.12.2016
 * Time: 15:34
 */

namespace CustomerManagementFramework\ActionTrigger\Condition;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractCondition implements ConditionInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function createConditionDefinitionFromEditmode($setting)
    {
        $setting = json_decode(json_encode($setting), true);
        return new \CustomerManagementFramework\ActionTrigger\ConditionDefinition($setting);
    }

    public static function getDataForEditmode(ConditionDefinitionInterface $conditionDefinition)
    {
        return $conditionDefinition->toArray();
    }
}