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

namespace CustomerManagementFrameworkBundle\Controller\Rest\Api;

use CustomerManagementFrameworkBundle\Controller\Rest\CrudHandlerController;
use CustomerManagementFrameworkBundle\RESTApi\SegmentGroupsHandler;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/segment-groups")
 */
class SegmentGroupsController extends CrudHandlerController
{
    /**
     * @return SegmentGroupsHandler
     */
    protected function getHandler(): SegmentGroupsHandler
    {
        return \Pimcore::getContainer()->get('cmf.rest.segment_groups_handler');
    }
}
