<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Action;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

interface ActionInterface
{
    public function __construct(LoggerInterface $logger);

    public function process(ActionDefinitionInterface $actionDefinition, CustomerInterface $customer);

    public static function createActionDefinitionFromEditmode(\stdClass $data);

    public static function getDataForEditmode(ActionDefinitionInterface $actionDefinition);
}
