<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 05.12.2016
 * Time: 14:32
 */

namespace CustomerManagementFramework\ActionTrigger\Condition;

interface ConditionDefinitionInterface {
    public function __construct(array $definitionData);

    /**
     * @return string
     */
    public function getImplementationClass();

    /**
     * @return ConditionInterface
     */
    public function getImplementationObject();

    /**
     * @return array
     */
    public function getDefinitionData();

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @return bool
     */
    public function getBracketLeft();

    /**
     * @return bool
     */
    public function getBracketRight();

    /**
     * @return bool
     */
    public function getOperator();



    /**
     * @return array
     */
    public function toArray();
    
}