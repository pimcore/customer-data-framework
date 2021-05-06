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

use CustomerManagementFrameworkBundle\ActionTrigger\Trigger\TriggerDefinitionInterface;

class TriggerDefinition implements TriggerDefinitionInterface
{
    private $definitionData;

    private $eventName;

    private $options;

    public function __construct(array $definitionData)
    {
        $this->definitionData = $definitionData;
        $this->eventName = $definitionData['eventName'];
        $this->options = isset($definitionData['options']) ? $definitionData['options'] : [];
    }

    public function getEventName()
    {
        return $this->eventName;
    }

    public function getDefinitionData()
    {
        return $this->definitionData;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function toArray()
    {
        return $this->getDefinitionData();
    }
}
