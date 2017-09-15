<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Controller\Preview;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\LinkActivityDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/object-preview")
 */
class ObjectPreviewController extends FrontendController
{

    /**
     * @return Response
     * @Route("/link-activity-definition-preview")
     */
    public function linkActivityDefinitionPreviewAction(Request $request)
    {
        $activityDefinition = LinkActivityDefinition::getById($request->get('pimcore_object_preview'));

        return $this->render(
            'PimcoreCustomerManagementFrameworkBundle:Preview\ObjectPreview:link-activity-definition-preview.html.php',
            [
                'activityDefinition' => $activityDefinition,
            ]
        );
    }
}
