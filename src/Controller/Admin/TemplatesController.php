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

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\TemplateExporter;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Model\Document\PageSnippet;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/templates")
 */
class TemplatesController extends UserAwareController
{
    use JsonHelperTrait;

    /**
     * @Route("/export")
     *
     * @throws \Exception
     */
    public function exportAction(Request $request, TemplateExporter $templateExporter): JsonResponse
    {
        $document = PageSnippet::getById($request->get('document_id'));

        if ($document) {
            $templateExporter->exportTemplate($document);

            return $this->jsonResponse(['success' => true]);
        } else {
            throw new \Exception(sprintf('Document {%s} not found!', $request->get('document_id')));
        }
    }
}
