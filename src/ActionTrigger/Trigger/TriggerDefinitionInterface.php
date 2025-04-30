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

namespace CustomerManagementFrameworkBundle\ActionTrigger\Trigger;

interface TriggerDefinitionInterface
{
    public function __construct(array $definitionData);

    public function getEventName();

    public function getDefinitionData();

    public function getOptions();

    public function toArray();
}
