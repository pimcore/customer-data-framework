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
     * @return string
     */
    public function getOperator();

    /**
     * @return array
     */
    public function toArray();
}
