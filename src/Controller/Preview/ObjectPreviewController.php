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
