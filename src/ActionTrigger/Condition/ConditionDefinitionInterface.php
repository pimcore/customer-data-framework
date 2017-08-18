<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
     * @param array $options
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
     * @return bool
     */
    public function getOperator();

    /**
     * @return array
     */
    public function toArray();
}
