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

namespace CustomerManagementFrameworkBundle\Controller\Report;

use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\TermSegmentBuilderDefinition\Listing;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/term-segment-builder")
 */
class TermSegmentBuilderController extends UserAwareController
{
    use JsonHelperTrait;

    /**
     * @Route("/get-segment-builder-definitions")
     */
    public function getSegmentBuilderDefinitionsAction(): JsonResponse
    {
        AbstractObject::setHideUnpublished(true);

        $list = new Listing;
        $list = $list->load();

        $result = ['data' => []];

        foreach ($list as $entry) {
            $result['data'][] = [
                'id' => $entry->getId(),
                'name' => $entry->getName(),
            ];
        }

        return $this->jsonResponse($result);
    }
}
