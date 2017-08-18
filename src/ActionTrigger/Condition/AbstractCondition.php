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

use Psr\Log\LoggerInterface;

abstract class AbstractCondition implements ConditionInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function createConditionDefinitionFromEditmode($setting)
    {
        $setting = json_decode(json_encode($setting), true);

        return new \CustomerManagementFrameworkBundle\Model\ActionTrigger\ConditionDefinition($setting);
    }

    public static function getDataForEditmode(ConditionDefinitionInterface $conditionDefinition)
    {
        return $conditionDefinition->toArray();
    }
}
