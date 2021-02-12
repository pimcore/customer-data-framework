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

namespace CustomerManagementFrameworkBundle\Event\Newsletter\Mailchimp;

use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;
use Symfony\Component\EventDispatcher\Event;
use Pimcore\Model\Document;

class TemplateExportResolveProviderHandlerEvent extends Event
{
    const NAME = 'plugin.cmf.newsletter.mailchimp.template-export-resolve-provider-handler';

    /**
     * @var Mailchimp|null
     */
    private $providerHandler;

    /**
     * @var Document\PageSnippet $document
     */
    private $document;

    /**
     * @param Document\PageSnippet $document
     */
    public function __construct(Document\PageSnippet $document)
    {
        $this->document = $document;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return Mailchimp|null
     */
    public function getProviderHandler(): ?Mailchimp
    {
        return $this->providerHandler;
    }

    /**
     * @return Document\PageSnippet
     */
    public function getDocument(): Document\PageSnippet
    {
        return $this->document;
    }

    /**
     * @param Mailchimp|null $providerHandler
     */
    public function setProviderHandler(Mailchimp $providerHandler)
    {
        $this->providerHandler = $providerHandler;
    }
}
