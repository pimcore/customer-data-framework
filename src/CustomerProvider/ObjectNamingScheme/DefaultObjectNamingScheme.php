<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\CustomerProvider\ObjectNamingScheme;

use CustomerManagementFrameworkBundle\Config;
use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\Object\Folder;
use Pimcore\Model\Object\Listing;
use Pimcore\Model\Object\Service;

class DefaultObjectNamingScheme implements ObjectNamingSchemeInterface
{
    use LoggerAware;

    /**
     * @param CustomerInterface $customer
     * @param string $parentPath
     * @param string $namingScheme
     *
     * @return void
     */
    public function apply(CustomerInterface $customer, $parentPath, $namingScheme)
    {
        if ($namingScheme) {
            $namingScheme = $this->extractNamingScheme($customer, $namingScheme);

            $key = $namingScheme[sizeof($namingScheme) - 1];
            unset($namingScheme[sizeof($namingScheme) - 1]);

            $parentPath .= '/'.implode('/', $namingScheme);
            $customer->setKey($key);
        }

        $parentPath = $this->correctPath($parentPath);

        $customer->setParent(Service::createFolderByPath($parentPath));

        if (!$customer->getKey()) {
            $customer->setKey(uniqid());
        }
        Objects::checkObjectKey($customer);
    }

    public function cleanupEmptyFolders()
    {
        $config = Config::getConfig()->CustomerProvider;
        if(!$config->parentPath) {
            return;
        }

        if(!$config->namingScheme) {
            return;
        }

        $folders = new Listing;

        // apply it for folders older then 10 minutes only
        $timestamp = time() - 60*10;

        $folders->setCondition(
            "o_id in (select o_id from (select o_id, o_path, o_key, o_type, (select count(*) from objects where o_parentId = o.o_id) as counter from objects o) as temp where counter=0 and o_type = 'folder' and o_path like ?  and o_creationDate < ?)",
            [
                str_replace('//', '/', $config->parentPath.'/%'),
                $timestamp
            ]
        );

        foreach($folders as $folder) {
            if($folder instanceof Folder) {
                $folder->delete();
                $this->getLogger()->error("delete empty folder ". (string) $folder);
            }
        }
    }


    private function correctPath($path)
    {
        return str_replace('//', '/', $path);
    }

    /**
     * @param CustomerInterface $customer
     * @param $namingScheme
     *
     * @return array
     */
    private function extractNamingScheme(CustomerInterface $customer, $namingScheme)
    {
        $namingScheme = explode('/', $namingScheme);
        foreach ($namingScheme as $i => $namingSchemeItem) {
            preg_match_all('/{([a-zA-Z0-9]*)}/', $namingSchemeItem, $matchedPlaceholder);

            if (sizeof($matchedPlaceholder)) {
                foreach($matchedPlaceholder[0] as $j => $placeholder) {
                    $field = $matchedPlaceholder[1][$j];

                    $getter = 'get'.ucfirst($field);
                    if (method_exists($customer, $getter)) {
                        $value = (string)$customer->$getter();
                        $value = $value ?: '--';
                        $namingScheme[$i] = str_replace($placeholder, $value, $namingScheme[$i]);
                    }
                }
            }
            $namingScheme[$i] = Objects::getValidKey($namingScheme[$i]);
        }

        return $namingScheme;
    }
}
