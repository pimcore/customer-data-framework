<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Model\ActivityList;

use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Paginator\AdapterAggregateInterface;

interface ActivityListInterface extends AdapterInterface, AdapterAggregateInterface, \Iterator
{
    public function setCondition($condition, $conditionVariables = null);
}
