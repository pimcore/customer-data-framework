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

    /**
     * ConditionInterface constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger);

    /**
     * @param ConditionDefinitionInterface $conditionDefinition
     * @param CustomerInterface            $customer
     *
     * @return bool
     */
    public function check(ConditionDefinitionInterface $conditionDefinition, CustomerInterface $customer);

    /**
     * @param $setting
     *
     * @return ConditionDefinitionInterface
     */
    public static function createConditionDefinitionFromEditmode($setting);

    /**
     * @param ConditionDefinitionInterface $conditionDefinition
     *
     * @return array
     */
    public static function getDataForEditmode(ConditionDefinitionInterface $conditionDefinition);
}