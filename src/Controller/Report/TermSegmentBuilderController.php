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

namespace CustomerManagementFrameworkBundle\Controller\Report;

use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/term-segment-builder")
 */
class TermSegmentBuilderController extends AdminController
{
    /**
     * @Route("/get-segment-builder-definitions")
     */
    public function getSegmentBuilderDefinitionsAction()
    {
        \Pimcore\Model\DataObject\AbstractObject::setHideUnpublished(true);

        $list = new \Pimcore\Model\DataObject\TermSegmentBuilderDefinition\Listing;
        $list = $list->load();

        $result = ['data' => []];

        foreach ($list as $entry) {
            $result['data'][] = [
                'id' => $entry->getId(),
                'name' => $entry->getName(),
            ];
        }

        return $this->adminJson($result);
    }
}
