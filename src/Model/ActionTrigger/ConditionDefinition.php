<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\Model\ActionTrigger;

use CustomerManagementFrameworkBundle\ActionTrigger\Condition\ConditionDefinitionInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Condition\ConditionInterface;
use CustomerManagementFrameworkBundle\Factory;

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
        $this->options = $definitionData['options'] ?? [];
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

        if (class_exists($class)) {
            return Factory::getInstance()->createObject(
                $class,
                ConditionInterface::class,
                ['logger' => \Pimcore::getContainer()->get('cmf.logger')]
            );
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
     * @return bool
     */
    public function getBracketLeft()
    {
        return $this->bracketLeft;
    }

    /**
     * @return bool
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
