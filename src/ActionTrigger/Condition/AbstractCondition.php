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
