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

namespace CustomerManagementFrameworkBundle\Event\Newsletter\Mailchimp;

use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;
use Pimcore\Model\Document;
use Symfony\Contracts\EventDispatcher\Event;

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

    public function __construct(Document\PageSnippet $document)
    {
        $this->document = $document;
    }

    public function getName()
    {
        return self::NAME;
    }

    public function getProviderHandler(): ?Mailchimp
    {
        return $this->providerHandler;
    }

    public function getDocument(): Document\PageSnippet
    {
        return $this->document;
    }

    public function setProviderHandler(Mailchimp $providerHandler)
    {
        $this->providerHandler = $providerHandler;
    }
}
