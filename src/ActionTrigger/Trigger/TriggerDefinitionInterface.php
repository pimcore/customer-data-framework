<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
