<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Condition;

interface ConditionDefinitionInterface
{
    public function __construct(array $definitionData);

    /**
     * @return string
     */
    public function getImplementationClass();

    /**
     * @return ConditionInterface|false
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
     *
     * @return void
     */
    public function setOptions(array $options);

    /**
     * @return bool
     */
    public function getBracketLeft();

    /**
     * @return bool
     */
    public function getBracketRight();

    /**
     * @return string
     */
    public function getOperator();

    /**
     * @return array
     */
    public function toArray();
}
