<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\LinkGenerator;

use Pimcore\Model\DataObject\ClassDefinition\LinkGeneratorInterface;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\LinkActivityDefinition;
use Pimcore\Tool;

class LinkActivityDefinitionLinkGenerator implements LinkGeneratorInterface
{
    protected $cmfcPlaceholder;

    public function __construct($cmfcPlaceholder = '*|ENCODED_ID|*')
    {
        $this->cmfcPlaceholder = $cmfcPlaceholder;
    }

    /**
     * @param LinkActivityDefinition $object
     * @param array $params
     * @return string
     */
    public function generate(Concrete $object, array $params = []): string
    {
        $href = $object->getLink()->getHref();

        $url = new \Net_URL2($href);

        if(!$url->getHost()) {
            $url->setHost(Tool::getHostname());
            $url->setScheme(isset($_SERVER['HTTPS']) ? "https" : "http");
        }


        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $gaParam) {
            $getter = 'get' . ucfirst($gaParam);

            if($value = $object->$getter()) {
                $url->setQueryVariable($gaParam, $value);
            }
        }

        $url->setQueryVariable('cmfa', $object->getCode());
        $url->setQueryVariable('cmfc', $this->cmfcPlaceholder);


        return (string) $url;
    }

}
