<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
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
