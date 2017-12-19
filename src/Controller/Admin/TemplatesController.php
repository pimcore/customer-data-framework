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

            return $this->adminJson(['success' => true]);
        } else {
            throw new \Exception(sprintf('Document {%s} not found!', $request->get('document_id')));
        }
    }
}
