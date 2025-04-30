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

namespace CustomerManagementFrameworkBundle\Controller\Preview;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\LinkActivityDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/object-preview')]
class ObjectPreviewController extends FrontendController
{
    #[Route('/link-activity-definition-preview', name: 'cmf_link_activity_definition_preview')]
    public function linkActivityDefinitionPreviewAction(Request $request): Response
    {
        $activityDefinition = LinkActivityDefinition::getById($request->query->getInt('pimcore_object_preview'));

        return $this->render(
            '@PimcoreCustomerManagementFramework/preview/object_preview/link_activity_definition_preview.html.twig',
            [
                'activityDefinition' => $activityDefinition,
            ]
        );
    }
}
