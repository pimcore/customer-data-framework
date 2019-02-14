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

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;

use CustomerManagementFrameworkBundle\Traits\ApplicationLoggerAware;
use DrewM\MailChimp\MailChimp;
use Pimcore\Helper\Mail;
use Pimcore\Model\Document;

class TemplateExporter
{
    use ApplicationLoggerAware;

    const LIST_ID_PLACEHOLDER = 'global';

    /**
     * @var MailChimpExportService
     */
    private $exportService;

    /**
     * TemplateExporter constructor.
     *
     * @param MailChimp $apiClient
     * @param string $listId
     */
    public function __construct(MailChimpExportService $exportService)
    {
        $this->exportService = $exportService;

        $this->setLoggerComponent('NewsletterSync');
    }

    public function exportTemplate(Document\PageSnippet $document)
    {
        $exportService = $this->exportService;
        $apiClient = $exportService->getApiClient();

        $remoteId = $exportService->getRemoteId($document, self::LIST_ID_PLACEHOLDER);

        $html = \Pimcore\Model\Document\Service::render($document);

        //dirty hack to prevent absolutize unsubscribe url placeholder of mailchimp
        $html = str_replace(["*|UNSUB|*", "*|FORWARD|*", "*|UPDATE_PROFILE|*", "*|ARCHIVE|*"], ["data:*|UNSUB|*", "data:*|FORWARD|*", "data:*|UPDATE_PROFILE|*", "data:*|ARCHIVE|*"], $html);

        // modifying the content e.g set absolute urls...
        $html = Mail::embedAndModifyCss($html, $document);
        $html = Mail::setAbsolutePaths($html, $document);

        //dirty hack to make sure mailchimp merge tags are not url-encoded
        $html = str_replace("*%7C", "*|", $html);
        $html = str_replace("%7C*", "|*", $html);

        //dirty hack to prevent absolutize unsubscribe url placeholder of mailchimp
        $html = str_replace(["data:*|UNSUB|*", "data:*|FORWARD|*", "data:*|UPDATE_PROFILE|*", "data:*|ARCHIVE|*"], ["*|UNSUB|*", "*|FORWARD|*", "*|UPDATE_PROFILE|*", "*|ARCHIVE|*"], $html);


        $templateExists = false;

        //check if template really exists in MailChimp
        if ($remoteId) {
            $result = $apiClient->get("templates/$remoteId");
            if ($apiClient->success() && $result['id'] && $result['active']) {
                $templateExists = true;
            }
        }

        $templateName = substr($document->getKey(), 0, 35) . ' [ID ' . $document->getID() . ']';

        if ($remoteId && $templateExists) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp] Updating new Template with name %s based on document id %s',
                    $templateName,
                    $document->getId()
                )
            );

            $result = $apiClient->patch("templates/$remoteId", [
                'name' => $templateName,
                'html' => $html
            ]);
        } else {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][Template] Creating new template with name %s based on document id %s',
                    $templateName,
                    $document->getId()
                )
            );

            $result = $apiClient->post('templates', [
                'name' => $templateName,
                'html' => $html
            ]);
        }

        if ($apiClient->success()) {
            $remoteId = $result['id'];
            $exportNote = $exportService->createExportNote($document, self::LIST_ID_PLACEHOLDER, $remoteId);
            $exportNote->save();
        } else {
            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][Template] Failed to export template %s: %s %s',
                    $templateName,
                    json_encode($apiClient->getLastError()),
                    $apiClient->getLastResponse()['body']
                ),
                [
                    'relatedObject' => $document
                ]
            );

            throw new \Exception('[MailChimp] Creating new Template failed: ' . json_encode($apiClient->getLastError()));
        }
    }
}
