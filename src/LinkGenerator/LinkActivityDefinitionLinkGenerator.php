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

namespace CustomerManagementFrameworkBundle\LinkGenerator;

use Pimcore\Model\DataObject\ClassDefinition\LinkGeneratorInterface;
use Pimcore\Model\DataObject\LinkActivityDefinition;
use Pimcore\Model\Document;
use Pimcore\Model\Site;
use Pimcore\Tool;

class LinkActivityDefinitionLinkGenerator implements LinkGeneratorInterface
{
    protected $cmfcPlaceholder;

    public function __construct($cmfcPlaceholder = '*|ID_ENCODED|*')
    {
        $this->cmfcPlaceholder = $cmfcPlaceholder;
    }

    /**
     * @param LinkActivityDefinition $object
     * @param array $params
     *
     * @return string
     */
    public function generate(object $object, array $params = []): string
    {

        // workarround to let it work in cross site links
        if (!Site::isSiteRequest()) {
            $site = new Site();
            $site->setRootDocument(Document\Page::getById(1));
            Site::setCurrentSite($site);
        }

        if (!$object->getLink()) {
            return '';
        }

        $href = $object->getLink()->getHref();

        $url = new \Net_URL2($href);

        if (!$url->getHost()) {
            $url->setHost(Tool::getHostname());
            $url->setScheme(isset($_SERVER['HTTPS']) ? 'https' : 'http');
        }

        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $gaParam) {
            $getter = 'get' . ucfirst($gaParam);

            if ($value = $object->$getter()) {
                $url->setQueryVariable($gaParam, $value);
            }
        }

        $url->setQueryVariable('cmfa', $object->getCode());
        $url->setQueryVariable('cmfc', $this->cmfcPlaceholder);

        $url = $url->getURL();

        //make sure that cmfcPlaceholder is not urlencoded
        $url = str_replace(rawurlencode($this->cmfcPlaceholder), $this->cmfcPlaceholder, $url);

        return $url;
    }
}
