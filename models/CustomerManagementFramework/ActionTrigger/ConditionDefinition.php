<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 16:44
 */

namespace CustomerManagementFramework\ActionTrigger;

use CustomerManagementFramework\ActionTrigger\Condition\ConditionDefinitionInterface;
use CustomerManagementFramework\ActionTrigger\Condition\ConditionInterface;
use CustomerManagementFramework\Factory;

class ConditionDefinition implements ConditionDefinitionInterface
{
    private $definitionData;

    private $options;

    private $implementationClass;

    /**
     * @var bool
     */
    private $bracketLeft;

    /**
     * @var bool
     */
    private $bracketRight;

    /**
     * @var string
     */
    private $operator;

    public function __construct(array $definitionData)
    {
        $this->definitionData = $definitionData;
        $this->implementationClass = $definitionData['implementationClass'];
        $this->options = isset($definitionData['options']) ? $definitionData['options'] : [];
        $this->bracketLeft = $definitionData['bracketLeft'];
        $this->bracketRight = $definitionData['bracketRight'];
        $this->operator = $definitionData['operator'];
    }

    public function getImplementationClass()
    {
        return $this->implementationClass;
    }
    public function getImplementationObject()
    {
        $class = $this->getImplementationClass();

        if(class_exists($class)) {
            return Factory::getInstance()->createObject($class, ConditionInterface::class, [\Pimcore::getDiContainer()->get("CustomerManagementFramework\\Logger")]);
        }

        return false;
    }

    public function getDefinitionData()
    {
        return $this->definitionData;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
        $this->definitionData['options'] = $options;
    }

    public function toArray()
    {
        return $this->getDefinitionData();
    }

    /**
     * @return boolean
     */
    public function getBracketLeft()
    {
        return $this->bracketLeft;
    }

    /**
     * @return boolean
     */
    public function getBracketRight()
    {
        return $this->bracketRight;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }


}