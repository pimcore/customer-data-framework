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
