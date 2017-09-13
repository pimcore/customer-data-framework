<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\TemplateExporter;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\Document\PageSnippet;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/templates")
 */
class TemplatesController extends AdminController
{
    /**
     * @param Request $request
     * @param TemplateExporter $templateExporter
     *
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     *
     * @throws \Exception
     * @Route("/export")
     */
    public function exportAction(Request $request)
    {
        $templateExporter = $this->get(TemplateExporter::class);
        $document = PageSnippet::getById($request->get('document_id'));

        if ($document) {
            $templateExporter->exportTemplate($document);

            return $this->json(['success' => true]);
        } else {
            throw new \Exception(sprintf('Document {%s} not found!', $request->get('document_id')));
        }
    }
}
