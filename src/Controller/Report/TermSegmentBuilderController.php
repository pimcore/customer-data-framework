<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
        \Pimcore\Model\Object\AbstractObject::setHideUnpublished(true);

        $list = new \Pimcore\Model\Object\TermSegmentBuilderDefinition\Listing;
        $list = $list->load();

        $result = ['data' => []];

        foreach ($list as $entry) {
            $result['data'][] = [
                'id' => $entry->getId(),
                'name' => $entry->getName(),
            ];
        }

        return $this->json($result);
    }
}
